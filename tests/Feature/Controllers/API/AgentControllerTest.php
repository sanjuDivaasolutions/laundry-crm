<?php

namespace Tests\Feature\Controllers\API;

use App\Models\User;
use App\Models\Agent;
use App\Models\AgentCommission;
use App\Models\SalesOrder;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class AgentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Agent $agent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->agent = Agent::factory()->create([
            'user_id' => $this->user->id,
        ]);
    }

    public function test_can_list_agents()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/agents');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'code',
                        'name',
                        'email',
                        'commission_rate',
                        'commission_type',
                        'active',
                    ]
                ]
            ]);
    }

    public function test_can_create_agent()
    {
        $agentData = [
            'code' => 'AGENT-001',
            'name' => 'Test Agent',
            'email' => 'agent@test.com',
            'commission_rate' => 10.00,
            'commission_type' => 'percentage',
            'fixed_commission' => 0.00,
            'active' => true,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/agents', $agentData);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'code' => 'AGENT-001',
                'name' => 'Test Agent',
                'email' => 'agent@test.com',
                'commission_rate' => 10.00,
                'commission_type' => 'percentage',
            ]);

        $this->assertDatabaseHas('agents', [
            'code' => 'AGENT-001',
            'email' => 'agent@test.com',
        ]);
    }

    public function test_can_view_agent()
    {
        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/agents/{$this->agent->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $this->agent->id,
                'code' => $this->agent->code,
                'name' => $this->agent->name,
            ]);
    }

    public function test_can_update_agent()
    {
        $updateData = [
            'name' => 'Updated Agent Name',
            'commission_rate' => 15.00,
        ];

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/agents/{$this->agent->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'Updated Agent Name',
                'commission_rate' => 15.00,
            ]);

        $this->assertDatabaseHas('agents', [
            'id' => $this->agent->id,
            'name' => 'Updated Agent Name',
            'commission_rate' => 15.00,
        ]);
    }

    public function test_can_delete_agent()
    {
        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/agents/{$this->agent->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Agent deleted successfully']);

        $this->assertSoftDeleted('agents', [
            'id' => $this->agent->id,
        ]);
    }

    public function test_can_get_agent_commission_summary()
    {
        // Create some commissions for the agent
        AgentCommission::factory()->count(3)->create([
            'agent_id' => $this->agent->id,
            'status' => 'pending',
            'commission_amount' => 100.00,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/agents/{$this->agent->id}/commission-summary");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'total_commissions',
                'total_amount',
                'pending_count',
                'pending_amount',
            ]);
    }

    public function test_can_approve_commissions()
    {
        $commissions = AgentCommission::factory()->count(2)->create([
            'agent_id' => $this->agent->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/agents/commissions/approve', [
                'commission_ids' => $commissions->pluck('id')->toArray(),
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'approved_count' => 2,
            ]);

        foreach ($commissions as $commission) {
            $this->assertDatabaseHas('agent_commissions', [
                'id' => $commission->id,
                'status' => 'approved',
                'approved_by' => $this->user->id,
            ]);
        }
    }

    public function test_can_mark_commissions_as_paid()
    {
        $commissions = AgentCommission::factory()->count(2)->create([
            'agent_id' => $this->agent->id,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/agents/commissions/mark-paid', [
                'commission_ids' => $commissions->pluck('id')->toArray(),
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'paid_count' => 2,
            ]);

        foreach ($commissions as $commission) {
            $this->assertDatabaseHas('agent_commissions', [
                'id' => $commission->id,
                'status' => 'paid',
                'paid_by' => $this->user->id,
            ]);
        }
    }

    public function test_validation_fails_for_invalid_data()
    {
        $invalidData = [
            'code' => '',
            'name' => '',
            'email' => 'invalid-email',
            'commission_rate' => 150.00, // Invalid: > 100
            'commission_type' => 'invalid-type',
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/agents', $invalidData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'code',
                'name',
                'email',
                'commission_rate',
                'commission_type',
            ]);
    }

    public function test_unauthorized_user_cannot_access_agents()
    {
        $response = $this->getJson('/api/v1/agents');

        $response->assertStatus(401);
    }
}
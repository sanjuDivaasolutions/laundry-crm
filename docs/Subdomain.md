TASK OBJECTIVE:
Design and implement automatic subdomain-based tenant routing for a Laravel + Vue SaaS platform.

Requirements:
- Each tenant has a unique subdomain (slug.myapp.com)
- Tenant is resolved from subdomain on every request
- Wildcard DNS assumed (*.myapp.com)
- SSL must work for all subdomains
- Admin panel runs on admin.myapp.com
- Invalid or suspended tenants must be blocked
- Must integrate with existing tenant_id global scope
- Must support future custom domains

Deliverables:
1. Database schema changes (tenant.slug, indexes)
2. Laravel middleware for tenant resolution
3. Tenant context container design
4. Global scope integration
5. Admin domain bypass logic
6. Signup flow changes
7. Subdomain validation rules
8. Local development setup guide
9. Security risks and mitigations
10. Rollback and migration plan

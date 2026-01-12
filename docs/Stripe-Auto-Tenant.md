TASK OBJECTIVE:
Design and implement automatic tenant creation and subdomain provisioning after successful Stripe payment.

Requirements:
- Stripe Checkout metadata carries tenant info
- Webhook must be idempotent
- Tenant slug must be unique and validated
- Tenant created inside DB transaction
- Admin user auto-created
- Plan assigned correctly
- Tenant activated only after confirmed payment
- Welcome email sent asynchronously
- Support retry-safe webhook handling
- Handle collision and failure cases

Deliverables:
1. Stripe checkout creation payload
2. Webhook controller design
3. Idempotency storage schema
4. Tenant provisioning service
5. Error handling strategy
6. Migration plan
7. Security review
8. Test cases

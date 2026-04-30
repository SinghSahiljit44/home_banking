A comprehensive full-stack digital banking platform developed by Singh Sahiljjt and David Brembati. This application provides a secure and intuitive environment for managing essential banking services, designed with a focus on modular architecture and data integrity.  
🚀 Technical Ecosystem

    Backend Framework: PHP 8.x with Laravel (MVC Architecture).  

    Database Management: MySQL powered by Eloquent ORM for advanced data modeling and relational mapping.  

    Frontend Technologies: JavaScript (ES6+), HTML5, and CSS3, with Bootstrap for a responsive, professional UI.  

    Development Methodology: Developed using Agile principles, ensuring an iterative and high-quality evolution of features.  

👥 Multi-Role Authorization (RBAC)

The system implements a strict Role-Based Access Control (RBAC) model with three specialized user tiers:  

    Clients: Manage personal profiles, view real-time balances/transaction history, and execute instant transfers (internal or simulated external).  

    Employees: Act as branch agents with the authority to process deposits and withdrawals, register new clients, and manage assigned customer accounts.  

    Administrators: Maintain full system oversight, including user management (Create/Read/Update/Delete), account status control (locking/unlocking), and detailed transaction reporting.  

🛠️ Architectural Excellence & Design Patterns

    Unified Logic: Utilizes a TransactionService acting as a Facade pattern to centralize banking logic and ensure consistency across all financial operations.  

    Security Middleware: Custom RoleMiddleware handles advanced authentication and authorization, verifying user status and permissions before granting access to sensitive routes.  

    Data Patterns: Implements the Repository Pattern via Laravel Models and the Decorator Pattern through Blade templating for clean separation of concerns.  

    SOLID Principles: Controllers are designed with the Single Responsibility Principle (GRASP) to ensure high cohesion and low coupling.  

🔒 Security Implementation

    Cryptographic Hashing: Employs one-way hashing for all user passwords and security question answers, ensuring that even system administrators cannot view raw credentials.  

    Account Safety: Features robust "Account Locking" and "User Blocking" mechanisms to prevent unauthorized activity or access during security incidents.  

    Internal Security: Custom authentication layers (independent of standard kits like Jetstream) provide a tailored, secure login experience via direct database interrogation.  

📈 Functional Highlights

    Instant Transactions: All deposits, withdrawals, and internal transfers are processed instantly.  

    Audit Trail: Detailed transaction logs including unique reference codes, status tracking, and IBAN-level detail for both sender and receiver.  

    Relational Integrity: Complex database relationships (e.g., ternary assignments between admins, employees, and clients) are managed through a structured schema and Eloquent relationships.

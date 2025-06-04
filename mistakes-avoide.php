 👇👇👇👇👇👇What my Mistakes should avoid👇👇👇👇👇👇



 0) decide proper response structure & code structure

 1) follow proper folder structure from scratch

 2) follow proper validation message and commenting correction

 3) do not use same route naming for api and web routes

 4) set api call as [ token authentication in backend api calls and key authentication in frontend api call ]

 5) avoid online links and place put it after download

 6) testing each module by all prospects [ along with security purpose ]

 7) do not keep any error in project [ in command line also ]

 8) remove console error from scratch and clean code should use

 9) make reusable function and from starting [ use helper , repositories , common function , treats ]

 10) follow naming conventions

 11) for all forms use display field errors , required with * , back and submit button , make form less scrollable in
 admin side

 12) keep daily and monthly backup untill project runs keep 15 days backup file

 13)




 👇👇👇👇👇👇What Deepseek Says 👇👇👇👇👇👇

 Here are more tips for code evaluation, categorized into general best practices and Laravel/PHP-specific ones:

 ---

 ### **General Best Practices (All Languages)**

 12) **Code Consistency**
 - Follow consistent indentation, spacing, and brace style.
 - Use linters/formatters (ESLint, Prettier, PHP_CodeSniffer).

 13) **Security**
 - Sanitize all inputs/outputs to prevent XSS, SQL injection, CSRF, etc.
 - Use prepared statements for database queries.
 - Avoid hardcoding sensitive data (use environment variables).

 14) **Performance**
 - Optimize database queries (avoid N+1 issues, use indexing).
 - Cache frequently accessed data.
 - Minimize external API calls; use async processing where needed.

 15) **Error Handling**
 - Log meaningful errors (avoid exposing sensitive data).
 - Use try-catch blocks for critical operations.
 - Provide user-friendly error messages.

 16) **Testing**
 - Write unit/integration tests (PHPUnit, Pest).
 - Test edge cases (empty data, invalid inputs, timeout scenarios).
 - Include security testing (OWASP ZAP, SonarQube).

 17) **Documentation**
 - Document APIs (Swagger/OpenAPI).
 - Add inline comments for complex logic.
 - Maintain a `README.md` for setup/deployment.

 18) **Version Control**
 - Use meaningful commit messages.
 - Follow Git flow (feature branches, pull requests).
 - Avoid committing large files (use `.gitignore`).

 19) **Scalability**
 - Design modular code for easy extension.
 - Avoid tight coupling (use dependency injection).

 20) **Accessibility**
 - Follow WCAG guidelines for frontend (alt text, ARIA labels).

 21) **Avoid Tech Debt**
 - Refactor redundant code early.
 - Address TODOs/FIXMEs promptly.

 ---

 ### **Laravel/PHP-Specific Tips**

 22) **Laravel Structure**
 - Follow MVC strictly (fat models, thin controllers).
 - Use Service/Repository pattern for business logic.

 23) **Eloquent Best Practices**
 - Use eager loading (`with()`) to avoid N+1 queries.
 - Leverage scopes, accessors/mutators.
 - Avoid queries in loops.

 24) **API Development**
 - Use API resources (`JsonResource`) for consistent responses.
 - Version APIs (`/v1/endpoint`).

 25) **Middleware**
 - Use middleware for auth, logging, CORS.
 - Apply rate limiting to APIs.

 26) **Validation**
 - Use Form Requests for complex validation.
 - Customize validation messages in `lang/`.

 27) **Blade Templates**
 - Avoid business logic in views.
 - Use components/slots for reusable UI.

 28) **Artisan Commands**
 - Create commands for cron jobs/queues.
 - Use `--quiet` flag in production.

 29) **Queue/Jobs**
 - Offload slow tasks to queues.
 - Use `failed_jobs` table for retries.

 30) **Configurations**
 - Cache config in production (`config:cache`).
 - Use `.env` for environment-specific values.

 31) **Dependency Injection**
 - Type-hint dependencies in constructors.
 - Bind interfaces to implementations.

 32) **Testing in Laravel**
 - Use factories for test data.
 - Mock external services.

 33) **Frontend Integration**
 - Use Laravel Mix for assets (avoid CDNs in prod).
 - Inertia.js/Vue/React for SPAs.

 ---

 ### **Final Checklist Before Deployment**
 - [ ] All tests pass.
 - [ ] Security audit done (CSRF, XSS, SQLi).
 - [ ] Environment variables validated.
 - [ ] Logging/monitoring setup (Sentry, Bugsnag).
 - [ ] Backup/rollback plan in place.

 Would you like me to elaborate on any specific area?







 👇👇👇👇👇👇👇👇👇what gpt says👇👇👇👇👇👇👇👇---

 ## ✅ **General (Common for All Languages)**

 ### 🔧 **Code Architecture & Structure**

 1. Define clear **project architecture** before starting (e.g., MVC, clean architecture, layered, etc.).
 2. Maintain a consistent and logical **folder/module structure**.
 3. Avoid **monolithic files** – split code into manageable modules/classes.
 4. Use **design patterns** (e.g., Singleton, Factory, Strategy) appropriately.
 5. Implement **SOLID principles** to ensure scalable and maintainable code.

 ### 📐 **Code Quality**

 6. Follow **naming conventions** for variables, functions, classes, and files.
 7. Avoid **duplicate code** – follow DRY (Don’t Repeat Yourself).
 8. Use **descriptive comments** only where needed (avoid obvious comments).
 9. Maintain **clean code** – no unused variables, functions, commented-out code.
 10. Format code properly using linters/auto-formatters (e.g., Prettier, ESLint, PHP-CS-Fixer).
 11. Use **typed variables** and return types if the language supports it.

 ### 🔐 **Security & Validation**

 12. Validate all inputs (frontend + backend).
 13. Sanitize data before saving or displaying.
 14. Use **CSRF, XSS, SQL injection** protections.
 15. **Error handling**: No raw error exposure to users; use logging.
 16. Ensure **authentication & authorization** for restricted routes.

 ### 🧪 **Testing & Debugging**

 17. Write **unit tests**, **integration tests**, and **e2e tests**.
 18. Remove all **console.log/debugger**/print statements.
 19. No runtime or **build-time errors/warnings** should remain.
 20. **Test every module from functional + security aspects**.

 ### 📊 **UI/UX & Forms**

 21. Always show **validation errors near fields**.
 22. Use **“\*” for required fields**, clearly mark them.
 23. Keep admin forms **short-scroll or accordion style**.
 24. Include **back** and **submit** buttons on all forms.

 ### ♻️ **Reusability**

 25. Write **helper functions**, **utility classes**, and **common components** from day one.
 26. Avoid logic inside views/templates – move it to helper/service layer.

 ---

 ## 🛠️ **Laravel/PHP-Specific**

 ### 🔄 **Routing & Middleware**

 1. Never use the **same name for API and web routes**.
 2. Group routes logically (`api.php`, `web.php`) and use middleware properly.
 3. Implement **Token Authentication (Sanctum/Passport)** for APIs.
 4. Use **API keys or rate limiting** for public APIs.

 ### 📁 **Structure & Naming**

 5. Follow Laravel's **default structure** (Models, Controllers, Views, Requests).
 6. Use **Service Providers**, **Repositories**, **Traits**, and **Jobs** appropriately.
 7. Group related logic using **Modules** or **Domain-Driven Design (DDD)**.

 ### 🛡️ **Validation & Security**

 8. Use **Form Request Validation** with custom messages.
 9. Never trust data coming from the frontend – always validate again in backend.
 10. Hide `.env` and sensitive files; never expose debug info.

 ### 🔄 **Code Reusability**

 11. Use **custom helper functions** (`app/helpers.php`) for common tasks.
 12. Use **Laravel Collections** and **Eloquent accessors/mutators**.
 13. Prefer **query scopes**, **policies**, and **gates** over inline permission logic.

 ### ⚙️ **Command Line & Environment**

 14. No warnings or errors should appear in **Artisan commands** or `php artisan serve`.
 15. Always keep the project **.env.sample** file updated.
 16. Use **queues** for long-running jobs (emails, imports).

 ### 🔧 **Optimization & Maintenance**

 17. Cache routes/config/views when deploying:
 `php artisan config:cache`, `route:cache`, etc.
 18. Run **composer audit** and **npm audit fix** regularly.
 19. Optimize asset loading using **Laravel Mix/Vite**.

 ### 📋 **Deployment & Readiness**

 20. Always keep a **README.md** with setup instructions.
 21. Maintain **API documentation** (Postman, Swagger, Laravel Scribe).
 22. Use **version control best practices** – branch naming, commits, PR reviews.
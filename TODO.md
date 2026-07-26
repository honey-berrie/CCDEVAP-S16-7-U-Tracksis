# Project TODO & Meeting Notes

##  Architecture & Refactoring
- [ ] Restructure project files into MVC architecture.
- [ ] Group views by user role (Admin, Coordinator, Advisor).
- [ ] **Decision needed:** Determine if Dark Mode is tied to the User account or the Browser/Device.

##  Admin View
- [ ] **BUG FIX:** Fix "Assignment of students" feature (currently not working).
- [ ] Implement "Admin assign members" functionality.
- [ ] Ensure Admin view has full access to everything, including viewing student document submissions.
- [ ] Implement sticky navigation bar (Phase 3).

##  UI/UX & General Features
- [ ] Replace all standard `alert()` notifications with custom Modal components.
- [ ] Implement and display user statuses ('In a group' vs. 'Not in a group').
- [ ] Update text/content for announcements in the Student view.

##  Strategy & Milestones
- [ ] **Workflow:** Adopt a top-down approach. Build all features in the Admin view first, then distribute/limit features for the other user roles.
- [ ] **Next Demo Goal:** Ensure all currently existing features are fully functional and bug-free.

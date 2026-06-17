/* ============================================================
   mock-data.js
   Static mock data simulating backend responses for Phase 1.
   Frontend-only: no real persistence, everything resets on reload.
   Shared by Administrator and Thesis Coordinator pages.
   ============================================================ */

/* ---------- Users (Students, Advisers, Coordinators, Admins) ---------- */
const allUsers = [
  { id: "U-1001", firstName: "Juan", lastName: "Dela Cruz", email: "juan.delacruz@university.edu", role: "adviser", status: "active", dateJoined: "2023-08-14", group: null },
  { id: "U-1002", firstName: "Maria", lastName: "Santos", email: "maria.santos@university.edu", role: "adviser", status: "active", dateJoined: "2022-06-02", group: null },
  { id: "U-1003", firstName: "Ramon", lastName: "Villanueva", email: "ramon.villanueva@university.edu", role: "adviser", status: "active", dateJoined: "2021-01-20", group: null },
  { id: "U-1004", firstName: "Liza", lastName: "Reyes", email: "liza.reyes@university.edu", role: "adviser", status: "inactive", dateJoined: "2020-09-11", group: null },
  { id: "U-1005", firstName: "Carlo", lastName: "Mendoza", email: "carlo.mendoza@university.edu", role: "adviser", status: "active", dateJoined: "2023-02-01", group: null },
  { id: "U-1006", firstName: "Honey Grace", lastName: "Berrie", email: "honey.berrie@university.edu", role: "coordinator", status: "active", dateJoined: "2021-11-03", group: null },
  { id: "U-1007", firstName: "Patricia", lastName: "Lim", email: "patricia.lim@university.edu", role: "coordinator", status: "active", dateJoined: "2022-08-19", group: null },
  { id: "U-1008", firstName: "Gerald Justine", lastName: "Corpuz", email: "gerald.corpuz@university.edu", role: "admin", status: "active", dateJoined: "2020-01-15", group: null },
  { id: "U-1009", firstName: "Kyrelle", lastName: "Ramos", email: "kyrelle.ramos@university.edu", role: "admin", status: "active", dateJoined: "2020-01-15", group: null },
  { id: "U-2001", firstName: "Andrea", lastName: "Cruz", email: "andrea.cruz@student.university.edu", role: "student", status: "active", dateJoined: "2025-06-10", group: "G-001" },
  { id: "U-2002", firstName: "Miguel", lastName: "Torres", email: "miguel.torres@student.university.edu", role: "student", status: "active", dateJoined: "2025-06-10", group: "G-001" },
  { id: "U-2003", firstName: "Bea", lastName: "Fernandez", email: "bea.fernandez@student.university.edu", role: "student", status: "active", dateJoined: "2025-06-10", group: "G-001" },
  { id: "U-2004", firstName: "Josh", lastName: "Aquino", email: "josh.aquino@student.university.edu", role: "student", status: "active", dateJoined: "2025-06-11", group: "G-002" },
  { id: "U-2005", firstName: "Nicole", lastName: "Garcia", email: "nicole.garcia@student.university.edu", role: "student", status: "active", dateJoined: "2025-06-11", group: "G-002" },
  { id: "U-2006", firstName: "Paolo", lastName: "Ramirez", email: "paolo.ramirez@student.university.edu", role: "student", status: "active", dateJoined: "2025-06-11", group: "G-002" },
  { id: "U-2007", firstName: "Samantha", lastName: "Ocampo", email: "samantha.ocampo@student.university.edu", role: "student", status: "active", dateJoined: "2025-06-12", group: "G-003" },
  { id: "U-2008", firstName: "Lance", lastName: "Bautista", email: "lance.bautista@student.university.edu", role: "student", status: "active", dateJoined: "2025-06-12", group: "G-003" },
  { id: "U-2009", firstName: "Jasmine", lastName: "Soriano", email: "jasmine.soriano@student.university.edu", role: "student", status: "inactive", dateJoined: "2025-06-12", group: "G-004" },
  { id: "U-2010", firstName: "Erika", lastName: "Pascual", email: "erika.pascual@student.university.edu", role: "student", status: "active", dateJoined: "2025-06-13", group: "G-004" },
  { id: "U-2011", firstName: "Vince", lastName: "Castillo", email: "vince.castillo@student.university.edu", role: "student", status: "active", dateJoined: "2025-06-13", group: "G-005" },
  { id: "U-2012", firstName: "Trisha", lastName: "Navarro", email: "trisha.navarro@student.university.edu", role: "student", status: "active", dateJoined: "2025-06-13", group: "G-005" },
  { id: "U-2013", firstName: "Dominic", lastName: "Salazar", email: "dominic.salazar@student.university.edu", role: "student", status: "active", dateJoined: "2025-06-14", group: "G-006" },
  { id: "U-2014", firstName: "Faith", lastName: "Tolentino", email: "faith.tolentino@student.university.edu", role: "student", status: "active", dateJoined: "2025-06-14", group: "G-006" },
  { id: "U-2015", firstName: "Renzo", lastName: "Manalo", email: "renzo.manalo@student.university.edu", role: "student", status: "active", dateJoined: "2025-06-14", group: "G-007" },
  { id: "U-2016", firstName: "Kristine", lastName: "Domingo", email: "kristine.domingo@student.university.edu", role: "student", status: "active", dateJoined: "2025-06-15", group: "G-007" },
];

/* ---------- Thesis Courses (handled by coordinators) ---------- */
const courses = [
  { id: "C-01", code: "THS-401", name: "Thesis Writing 1", coordinatorId: "U-1006", term: "AY 2025-2026, 1st Sem" },
  { id: "C-02", code: "THS-402", name: "Thesis Writing 2", coordinatorId: "U-1007", term: "AY 2025-2026, 1st Sem" },
];

/* ---------- Thesis Groups ---------- */
const groups = [
  { id: "G-001", name: "Team Alpha", title: "AI-Based Traffic Management System", members: ["Andrea Cruz", "Miguel Torres", "Bea Fernandez"], adviserId: "U-1001", courseId: "C-01", batch: "2026", status: "active", progress: 45, milestonesTotal: 13, milestonesDone: 6, formedDate: "2025-06-10", lastUpdate: "2026-06-10" },
  { id: "G-002", name: "Team Helix", title: "Blockchain-Based Voting Verification Platform", members: ["Josh Aquino", "Nicole Garcia", "Paolo Ramirez"], adviserId: "U-1002", courseId: "C-01", batch: "2026", status: "active", progress: 62, milestonesTotal: 13, milestonesDone: 8, formedDate: "2025-06-11", lastUpdate: "2026-06-12" },
  { id: "G-003", name: "Team Nimbus", title: "Cloud-Based Inventory Forecasting Tool", members: ["Samantha Ocampo", "Lance Bautista"], adviserId: "U-1003", courseId: "C-01", batch: "2026", status: "active", progress: 23, milestonesTotal: 13, milestonesDone: 3, formedDate: "2025-06-12", lastUpdate: "2026-06-09" },
  { id: "G-004", name: "Team Quanta", title: "IoT-Based Smart Agriculture Monitoring System", members: ["Jasmine Soriano", "Erika Pascual"], adviserId: "U-1001", courseId: "C-02", batch: "2026", status: "active", progress: 78, milestonesTotal: 13, milestonesDone: 10, formedDate: "2025-06-13", lastUpdate: "2026-06-14" },
  { id: "G-005", name: "Team Vertex", title: "Mobile App for Campus Lost-and-Found Management", members: ["Vince Castillo", "Trisha Navarro"], adviserId: "U-1005", courseId: "C-02", batch: "2026", status: "active", progress: 8, milestonesTotal: 13, milestonesDone: 1, formedDate: "2025-06-13", lastUpdate: "2026-06-08" },
  { id: "G-006", name: "Team Solace", title: "Mental Health Peer Support Web Platform", members: ["Dominic Salazar", "Faith Tolentino"], adviserId: null, courseId: "C-02", batch: "2026", status: "pending", progress: 0, milestonesTotal: 13, milestonesDone: 0, formedDate: "2026-06-01", lastUpdate: "2026-06-01" },
  { id: "G-007", name: "Team Orbit", title: "Augmented Reality Campus Navigation System", members: ["Renzo Manalo", "Kristine Domingo"], adviserId: null, courseId: "C-01", batch: "2026", status: "pending", progress: 0, milestonesTotal: 13, milestonesDone: 0, formedDate: "2026-06-03", lastUpdate: "2026-06-03" },
  { id: "G-008", name: "Team Beacon", title: "Disaster Response Resource Allocation System", members: ["Liam Bonifacio", "Maris Tan"], adviserId: "U-1002", courseId: "C-01", batch: "2025", status: "completed", progress: 100, milestonesTotal: 13, milestonesDone: 13, formedDate: "2024-06-09", lastUpdate: "2025-03-20" },
  { id: "G-009", name: "Team Catalyst", title: "Predictive Maintenance System for Campus Facilities", members: ["Owen Macaraeg", "Diane Velasquez"], adviserId: "U-1003", courseId: "C-02", batch: "2025", status: "completed", progress: 100, milestonesTotal: 13, milestonesDone: 13, formedDate: "2024-06-11", lastUpdate: "2025-03-18" },
  { id: "G-010", name: "Team Drift", title: "Carpooling Coordination App for Commuting Students", members: ["Ella Lazaro", "Brix Manlapaz"], adviserId: "U-1005", courseId: "C-01", batch: "2025", status: "rejected", progress: 0, milestonesTotal: 13, milestonesDone: 0, formedDate: "2025-06-15", lastUpdate: "2025-06-18" },
  { id: "G-011", name: "Team Pulse", title: "Wearable-Based Student Wellness Monitoring System", members: ["Aldrin Buenaventura", "Cassie Ledesma"], adviserId: "U-1001", courseId: "C-02", batch: "2025", status: "completed", progress: 100, milestonesTotal: 13, milestonesDone: 13, formedDate: "2024-06-12", lastUpdate: "2026-06-05" },
];

/* ---------- Thesis Records (archived + active, for read-only search) ---------- */
const thesisRecords = groups.map(group => ({
  groupId: group.id,
  groupName: group.name,
  title: group.title,
  adviserId: group.adviserId,
  batch: group.batch,
  status: group.status,
  progress: group.progress,
}));

/* ---------- Archived Theses (subset, completed + approved for archive) ---------- */
const archivedTheses = [
  { id: "A-001", groupId: "G-008", groupName: "Team Beacon", title: "Disaster Response Resource Allocation System", adviserName: "Maria Santos", batch: "2025", archivedDate: "2025-04-02", archivedBy: "Gerald Justine Corpuz" },
  { id: "A-002", groupId: "G-009", groupName: "Team Catalyst", title: "Predictive Maintenance System for Campus Facilities", adviserName: "Ramon Villanueva", batch: "2025", archivedDate: "2025-03-29", archivedBy: "Gerald Justine Corpuz" },
];

/* ---------- System Announcements (Admin, system-wide) ---------- */
const systemAnnouncements = [
  { id: "SA-01", title: "Scheduled Maintenance: June 20, 12AM-4AM", body: "U-Tracksis will be unavailable during this window for scheduled server maintenance. Please save your work beforehand.", postedBy: "Gerald Justine Corpuz", datePosted: "2026-06-14", audience: "All Users" },
  { id: "SA-02", title: "AY 2026-2027 Academic Calendar Released", body: "The academic calendar for the next school year is now available under System Settings. Milestone deadlines will be adjusted accordingly.", postedBy: "Kyrelle Ramos", datePosted: "2026-06-10", audience: "All Users" },
  { id: "SA-03", title: "Reminder: Update Your Profile Information", body: "Please make sure your contact details are up to date so notifications reach you on time.", postedBy: "Gerald Justine Corpuz", datePosted: "2026-06-02", audience: "All Users" },
];

/* ---------- Course Announcements (Coordinator, course-scoped) ---------- */
const courseAnnouncements = [
  { id: "CA-01", courseId: "C-01", title: "Proposal Defense Schedule Posted", body: "Defense slots for Thesis Writing 1 groups are now posted. Check your group's assigned adviser for your time slot.", postedBy: "Honey Grace Berrie", datePosted: "2026-06-12" },
  { id: "CA-02", courseId: "C-01", title: "Reminder: Chapter 3 Deadline Extended", body: "Due to the recent holiday, the Chapter 3 deadline has been moved to the following week.", postedBy: "Honey Grace Berrie", datePosted: "2026-06-05" },
  { id: "CA-03", courseId: "C-02", title: "New Adviser Slots Open for Unassigned Groups", body: "Two advisers have opened up additional slots for groups still awaiting assignment.", postedBy: "Patricia Lim", datePosted: "2026-06-08" },
];

/* ---------- Adviser Assignment History ---------- */
const assignmentHistory = [
  { id: "AH-01", groupId: "G-001", groupName: "Team Alpha", adviserName: "Juan Dela Cruz", action: "Assigned", actor: "Honey Grace Berrie", date: "2025-06-15" },
  { id: "AH-02", groupId: "G-002", groupName: "Team Helix", adviserName: "Maria Santos", action: "Assigned", actor: "Honey Grace Berrie", date: "2025-06-16" },
  { id: "AH-03", groupId: "G-005", groupName: "Team Vertex", adviserName: "Liza Reyes", action: "Assigned", actor: "Patricia Lim", date: "2025-06-17" },
  { id: "AH-04", groupId: "G-005", groupName: "Team Vertex", adviserName: "Liza Reyes", action: "Reassigned to Carlo Mendoza", actor: "Patricia Lim", date: "2026-05-30" },
];

/* ---------- Analytics: Monthly Submission Stats (for Chart.js line/bar) ---------- */
const submissionStats = {
  labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun"],
  onTime: [18, 22, 19, 25, 28, 24],
  late: [4, 3, 6, 2, 3, 5],
};

/* ---------- Analytics: Progress Distribution (for Chart.js doughnut) ---------- */
const progressData = {
  labels: ["Completed", "On Track", "Needs Attention", "Overdue"],
  values: [2, 4, 2, 2],
};

/* ---------- Analytics: Adviser Workload (for Chart.js horizontal bar) ---------- */
const adviserWorkload = [
  { adviser: "Juan Dela Cruz", groups: 2 },
  { adviser: "Maria Santos", groups: 2 },
  { adviser: "Ramon Villanueva", groups: 1 },
  { adviser: "Liza Reyes", groups: 0 },
  { adviser: "Carlo Mendoza", groups: 1 },
];

/* ---------- Dashboard Summary Counters ---------- */
function getAdminSummary() {
  return {
    totalStudents: allUsers.filter(user => user.role === "student").length,
    totalAdvisers: allUsers.filter(user => user.role === "adviser").length,
    totalCoordinators: allUsers.filter(user => user.role === "coordinator").length,
    totalGroups: groups.length,
    activeTheses: groups.filter(group => group.status === "active").length,
    archivedTheses: archivedTheses.length,
    pendingReviews: groups.filter(group => group.status === "pending").length,
  };
}

function getCoordinatorSummary(courseId) {
  const courseGroups = groups.filter(group => group.courseId === courseId);
  return {
    pendingGroups: courseGroups.filter(group => group.status === "pending").length,
    assignedAdvisers: new Set(courseGroups.filter(group => group.adviserId).map(group => group.adviserId)).size,
    activeGroups: courseGroups.filter(group => group.status === "active").length,
    recentAnnouncements: courseAnnouncements.filter(announcement => announcement.courseId === courseId).length,
  };
}

function getAdviserName(adviserId) {
  if (!adviserId) return "Unassigned";
  const adviser = allUsers.find(user => user.id === adviserId);
  return adviser ? `${adviser.firstName} ${adviser.lastName}` : "Unassigned";
}

function getCourseName(courseId) {
  const course = courses.find(course => course.id === courseId);
  return course ? course.code : "—";
}

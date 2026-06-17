/**
 * Mock data for frontend development.
 * Replace with API calls when PHP backend is ready.
 */

const MOCK_NEIGHBORHOODS = [
  { id: 1, name: "Westlands", area: "Nairobi West" },
  { id: 2, name: "Kilimani", area: "Nairobi Central" },
  { id: 3, name: "Kasarani", area: "Nairobi East" },
  { id: 4, name: "Embakasi", area: "Nairobi East" },
  { id: 5, name: "Karen", area: "Nairobi South" },
  { id: 6, name: "Ruaraka", area: "Nairobi North" },
  { id: 7, name: "Pipeline", area: "Nairobi East" },
  { id: 8, name: "Dandora", area: "Nairobi East" },
  { id: 9, name: "Kibera", area: "Nairobi West" },
  { id: 10, name: "Parklands", area: "Nairobi North" },
];

const MOCK_REPORTS = [
  {
    id: 1,
    userId: 2,
    userName: "Jane Mwangi",
    neighborhoodId: 1,
    neighborhood: "Westlands",
    status: "available",
    notes: "Strong flow since 6 AM. Tank is filling normally.",
    reportedAt: "2026-06-14T08:30:00",
    verified: true,
  },
  {
    id: 2,
    userId: 3,
    userName: "Peter Ochieng",
    neighborhoodId: 3,
    neighborhood: "Kasarani",
    status: "none",
    notes: "No water since yesterday evening. Multiple households affected on Mwiki road.",
    reportedAt: "2026-06-14T07:15:00",
    verified: true,
  },
  {
    id: 3,
    userId: 4,
    userName: "Grace Wanjiku",
    neighborhoodId: 2,
    neighborhood: "Kilimani",
    status: "low",
    notes: "Trickle only. Pressure very low on 4th floor.",
    reportedAt: "2026-06-14T06:45:00",
    verified: false,
  },
  {
    id: 4,
    userId: 5,
    userName: "James Kamau",
    neighborhoodId: 4,
    neighborhood: "Embakasi",
    status: "scheduled",
    notes: "NCWSC announced supply restoration between 2–6 PM today.",
    reportedAt: "2026-06-13T18:00:00",
    verified: true,
  },
  {
    id: 5,
    userId: 6,
    userName: "Mary Njeri",
    neighborhoodId: 7,
    neighborhood: "Pipeline",
    status: "none",
    notes: "Burst pipe reported near stage. No supply in the area.",
    reportedAt: "2026-06-13T14:20:00",
    verified: true,
  },
  {
    id: 6,
    userId: 2,
    userName: "Jane Mwangi",
    neighborhoodId: 5,
    neighborhood: "Karen",
    status: "available",
    notes: "Normal supply. No issues reported.",
    reportedAt: "2026-06-13T10:00:00",
    verified: true,
  },
  {
    id: 7,
    userId: 7,
    userName: "David Otieno",
    neighborhoodId: 9,
    neighborhood: "Kibera",
    status: "low",
    notes: "Water available but only for 2 hours in the morning.",
    reportedAt: "2026-06-12T09:30:00",
    verified: false,
  },
  {
    id: 8,
    userId: 8,
    userName: "Faith Akinyi",
    neighborhoodId: 6,
    neighborhood: "Ruaraka",
    status: "available",
    notes: "Good pressure throughout the morning.",
    reportedAt: "2026-06-12T08:00:00",
    verified: true,
  },
];

const MOCK_NOTIFICATIONS = [
  {
    id: 1,
    userId: 1,
    type: "warning",
    title: "No water in Kasarani",
    message: "3 new reports confirm no supply in Kasarani since yesterday.",
    read: false,
    createdAt: "2026-06-14T07:30:00",
    neighborhoodId: 3,
  },
  {
    id: 2,
    userId: 1,
    type: "success",
    title: "Supply restored in Westlands",
    message: "Community reports indicate normal water flow in Westlands.",
    read: false,
    createdAt: "2026-06-14T08:45:00",
    neighborhoodId: 1,
  },
  {
    id: 3,
    userId: 1,
    type: "info",
    title: "Scheduled maintenance",
    message: "Embakasi: NCWSC announced supply between 2–6 PM today.",
    read: true,
    createdAt: "2026-06-13T18:00:00",
    neighborhoodId: 4,
  },
  {
    id: 4,
    userId: 1,
    type: "danger",
    title: "Pipeline burst reported",
    message: "Multiple reports of a burst pipe near Pipeline stage.",
    read: true,
    createdAt: "2026-06-13T14:30:00",
    neighborhoodId: 7,
  },
  {
    id: 5,
    userId: 1,
    type: "info",
    title: "Welcome to AquaWatch Nairobi",
    message: "Start by reporting water status in your neighbourhood or subscribing to alerts.",
    read: true,
    createdAt: "2026-06-10T10:00:00",
    neighborhoodId: null,
  },
];

const MOCK_USERS = [
  { id: 1, name: "Demo User", email: "demo@aquawatch.ke", role: "resident", neighborhoodId: 1, status: "active", joinedAt: "2026-05-01" },
  { id: 2, name: "Jane Mwangi", email: "jane.m@email.com", role: "resident", neighborhoodId: 1, status: "active", joinedAt: "2026-05-10" },
  { id: 3, name: "Peter Ochieng", email: "peter.o@email.com", role: "resident", neighborhoodId: 3, status: "active", joinedAt: "2026-05-12" },
  { id: 4, name: "Grace Wanjiku", email: "grace.w@email.com", role: "resident", neighborhoodId: 2, status: "active", joinedAt: "2026-05-15" },
  { id: 5, name: "Admin User", email: "admin@aquawatch.ke", role: "admin", neighborhoodId: null, status: "active", joinedAt: "2026-04-01" },
  { id: 6, name: "Suspended User", email: "suspended@email.com", role: "resident", neighborhoodId: 8, status: "suspended", joinedAt: "2026-05-20" },
];

const MOCK_TREND_DATA = {
  labels: ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"],
  available: [42, 38, 45, 40, 35, 48, 44],
  low: [18, 22, 15, 20, 25, 12, 16],
  none: [12, 15, 10, 14, 18, 8, 11],
};

const MOCK_MONTHLY_STATS = [
  { month: "Jan", availablePct: 58, outages: 42 },
  { month: "Feb", availablePct: 62, outages: 38 },
  { month: "Mar", availablePct: 55, outages: 45 },
  { month: "Apr", availablePct: 60, outages: 40 },
  { month: "May", availablePct: 57, outages: 43 },
  { month: "Jun", availablePct: 54, outages: 46 },
];

const STATUS_LABELS = {
  available: "Available",
  low: "Low Pressure",
  none: "No Water",
  scheduled: "Scheduled",
};

const STATUS_BADGE_CLASS = {
  available: "badge-available",
  low: "badge-low",
  none: "badge-none",
  scheduled: "badge-scheduled",
};

const MOCK_CURRENT_USER = {
  id: 1,
  name: "Demo User",
  email: "demo@aquawatch.ke",
  role: "resident",
  neighborhoodId: 1,
  neighborhood: "Westlands",
};

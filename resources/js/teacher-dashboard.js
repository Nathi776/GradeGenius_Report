const dashboardRoot = document.querySelector('[data-teacher-dashboard]');

if (dashboardRoot) {
    const selectedTerm = dashboardRoot.dataset.selectedTerm;
    const lastTermIndex = dashboardRoot.dataset.lastTermIndex;
    const termFourDataAvailable = selectedTerm === 'all' || selectedTerm === lastTermIndex;

    dashboardRoot.querySelectorAll('[data-term-four-only]').forEach((section) => {
        section.hidden = !termFourDataAvailable;
    });
    const unavailableMessage = dashboardRoot.querySelector('[data-term-unavailable]');
    if (unavailableMessage) {
        unavailableMessage.hidden = termFourDataAvailable;
    }

// ==========================================
    // 1. COMPREHENSIVE DATASET (3 SUBJECTS)
    // ==========================================
    const educatorData = {
      teacherName: dashboardRoot.dataset.teacherName,
      learnerPresence: JSON.parse(dashboardRoot.dataset.learnerPresence || "{}"),
      subjects: [
        {
          id: "tmath11",
          code: "TMATH-11",
          name: "Technical Mathematics",
          grade: "Grade 11",
          accent: "bg-butter",
          description: "Analytical Geometry, Circle Geometry, Mensuration & Trigonometry",
          quizzes: [
            { id: "tm_q1", title: "Quiz 1: Analytical Geometry & Gradients", dateSet: "12 Oct 2026", totalMarks: 30, topic: "Coordinate Geometry" },
            { id: "tm_q2", title: "Quiz 2: Mensuration (Cones & Spheres)", dateSet: "20 Oct 2026", totalMarks: 25, topic: "3D Geometry" },
            { id: "tm_q3", title: "Quiz 3: Trigonometric Ratios & Identities", dateSet: "29 Oct 2026", totalMarks: 40, topic: "Trigonometry" },
            { id: "tm_q4", title: "Quiz 4: Functions & Parabola Graphs", dateSet: "06 Nov 2026", totalMarks: 35, topic: "Algebraic Functions" }
          ],
          notes: [
            { id: "tm_n1", title: "Study Guide: Distance, Midpoint & Gradient Formulas", topic: "Coordinate Geometry", dateUploaded: "08 Oct 2026", avgTimeMins: 18 },
            { id: "tm_n2", title: "Formula Sheet: Surface Area & Volume of Solids", topic: "3D Geometry", dateUploaded: "16 Oct 2026", avgTimeMins: 14 },
            { id: "tm_n3", title: "Worked Examples: CAST Diagram & Reduction Formulae", topic: "Trigonometry", dateUploaded: "25 Oct 2026", avgTimeMins: 24 },
            { id: "tm_n4", title: "Revision Pack: Exam Prep Paper 1 & 2", topic: "Exam Revision", dateUploaded: "02 Nov 2026", avgTimeMins: 31 }
          ],
          students: [
            {
              id: "STU-2026-014",
              name: "Sizwe Nkosi",
              initials: "SN",
              avatarBg: "bg-sky",
              lastActive: "2 hours ago",
              remarks: "Sizwe demonstrates strong analytical reasoning in geometry. Encourage double-checking sign changes in trigonometry.",
              quizResults: [
                { quizId: "tm_q1", score: 27, status: "Completed", attempts: 1, submittedOn: "13 Oct 2026" },
                { quizId: "tm_q2", score: 22, status: "Completed", attempts: 1, submittedOn: "21 Oct 2026" },
                { quizId: "tm_q3", score: 33, status: "Completed", attempts: 2, submittedOn: "30 Oct 2026" },
                { quizId: "tm_q4", score: 30, status: "Completed", attempts: 1, submittedOn: "07 Nov 2026" }
              ],
              noteActivity: [
                { noteId: "tm_n1", read: true, timeSpent: "22 mins", lastOpened: "13 Oct 2026" },
                { noteId: "tm_n2", read: true, timeSpent: "16 mins", lastOpened: "20 Oct 2026" },
                { noteId: "tm_n3", read: true, timeSpent: "28 mins", lastOpened: "30 Oct 2026" },
                { noteId: "tm_n4", read: true, timeSpent: "35 mins", lastOpened: "14 Nov 2026" }
              ]
            },
            {
              id: "STU-2026-019",
              name: "Amahle Dlamini",
              initials: "AD",
              avatarBg: "bg-mint",
              lastActive: "1 day ago",
              remarks: "Top performer in Technical Mathematics. Consistently reads every study note before attempting quizzes.",
              quizResults: [
                { quizId: "tm_q1", score: 29, status: "Completed", attempts: 1, submittedOn: "12 Oct 2026" },
                { quizId: "tm_q2", score: 24, status: "Completed", attempts: 1, submittedOn: "20 Oct 2026" },
                { quizId: "tm_q3", score: 38, status: "Completed", attempts: 1, submittedOn: "29 Oct 2026" },
                { quizId: "tm_q4", score: 33, status: "Completed", attempts: 1, submittedOn: "06 Nov 2026" }
              ],
              noteActivity: [
                { noteId: "tm_n1", read: true, timeSpent: "25 mins", lastOpened: "11 Oct 2026" },
                { noteId: "tm_n2", read: true, timeSpent: "19 mins", lastOpened: "19 Oct 2026" },
                { noteId: "tm_n3", read: true, timeSpent: "30 mins", lastOpened: "28 Oct 2026" },
                { noteId: "tm_n4", read: true, timeSpent: "40 mins", lastOpened: "13 Nov 2026" }
              ]
            },
            {
              id: "STU-2026-023",
              name: "Thabo Mokoena",
              initials: "TM",
              avatarBg: "bg-butter",
              lastActive: "4 days ago",
              remarks: "Thabo missed Quiz 4 and has not opened the recent Trigonometry & Revision notes. Needs remedial support.",
              quizResults: [
                { quizId: "tm_q1", score: 15, status: "Completed", attempts: 1, submittedOn: "15 Oct 2026" },
                { quizId: "tm_q2", score: 11, status: "Completed", attempts: 2, submittedOn: "24 Oct 2026" },
                { quizId: "tm_q3", score: 16, status: "Completed", attempts: 1, submittedOn: "02 Nov 2026" },
                { quizId: "tm_q4", score: null, status: "Missed", attempts: 0, submittedOn: "—" }
              ],
              noteActivity: [
                { noteId: "tm_n1", read: true, timeSpent: "9 mins", lastOpened: "15 Oct 2026" },
                { noteId: "tm_n2", read: true, timeSpent: "7 mins", lastOpened: "23 Oct 2026" },
                { noteId: "tm_n3", read: false, timeSpent: "0 mins", lastOpened: "Never" },
                { noteId: "tm_n4", read: false, timeSpent: "0 mins", lastOpened: "Never" }
              ]
            },
            {
              id: "STU-2026-027",
              name: "Lerato Khumalo",
              initials: "LK",
              avatarBg: "bg-lavender",
              lastActive: "5 hours ago",
              remarks: "Steady improvement across the term. Great effort on mensuration calculations.",
              quizResults: [
                { quizId: "tm_q1", score: 21, status: "Completed", attempts: 1, submittedOn: "13 Oct 2026" },
                { quizId: "tm_q2", score: 19, status: "Completed", attempts: 1, submittedOn: "21 Oct 2026" },
                { quizId: "tm_q3", score: 28, status: "Completed", attempts: 1, submittedOn: "31 Oct 2026" },
                { quizId: "tm_q4", score: 26, status: "Completed", attempts: 1, submittedOn: "08 Nov 2026" }
              ],
              noteActivity: [
                { noteId: "tm_n1", read: true, timeSpent: "17 mins", lastOpened: "12 Oct 2026" },
                { noteId: "tm_n2", read: true, timeSpent: "15 mins", lastOpened: "20 Oct 2026" },
                { noteId: "tm_n3", read: true, timeSpent: "21 mins", lastOpened: "30 Oct 2026" },
                { noteId: "tm_n4", read: false, timeSpent: "0 mins", lastOpened: "Never" }
              ]
            },
            {
              id: "STU-2026-031",
              name: "Karabo Molefe",
              initials: "KM",
              avatarBg: "bg-coral",
              lastActive: "1 day ago",
              remarks: "Good grasp of basic concepts, though Quiz 4 was submitted slightly late. Keep engaging with revision packs.",
              quizResults: [
                { quizId: "tm_q1", score: 19, status: "Completed", attempts: 1, submittedOn: "14 Oct 2026" },
                { quizId: "tm_q2", score: 17, status: "Completed", attempts: 1, submittedOn: "22 Oct 2026" },
                { quizId: "tm_q3", score: 24, status: "Completed", attempts: 2, submittedOn: "01 Nov 2026" },
                { quizId: "tm_q4", score: 21, status: "Overdue", attempts: 1, submittedOn: "10 Nov 2026" }
              ],
              noteActivity: [
                { noteId: "tm_n1", read: true, timeSpent: "14 mins", lastOpened: "14 Oct 2026" },
                { noteId: "tm_n2", read: false, timeSpent: "0 mins", lastOpened: "Never" },
                { noteId: "tm_n3", read: true, timeSpent: "19 mins", lastOpened: "31 Oct 2026" },
                { noteId: "tm_n4", read: true, timeSpent: "22 mins", lastOpened: "12 Nov 2026" }
              ]
            },
            {
              id: "STU-2026-035",
              name: "Neo Sithole",
              initials: "NS",
              avatarBg: "bg-sky",
              lastActive: "3 hours ago",
              remarks: "Excellent work ethic and consistent quiz scores above 80%.",
              quizResults: [
                { quizId: "tm_q1", score: 25, status: "Completed", attempts: 1, submittedOn: "12 Oct 2026" },
                { quizId: "tm_q2", score: 21, status: "Completed", attempts: 1, submittedOn: "20 Oct 2026" },
                { quizId: "tm_q3", score: 34, status: "Completed", attempts: 1, submittedOn: "29 Oct 2026" },
                { quizId: "tm_q4", score: 29, status: "Completed", attempts: 1, submittedOn: "06 Nov 2026" }
              ],
              noteActivity: [
                { noteId: "tm_n1", read: true, timeSpent: "20 mins", lastOpened: "12 Oct 2026" },
                { noteId: "tm_n2", read: true, timeSpent: "16 mins", lastOpened: "19 Oct 2026" },
                { noteId: "tm_n3", read: true, timeSpent: "25 mins", lastOpened: "29 Oct 2026" },
                { noteId: "tm_n4", read: true, timeSpent: "30 mins", lastOpened: "14 Nov 2026" }
              ]
            },
            {
              id: "STU-2026-042",
              name: "Zanele Ndlovu",
              initials: "ZN",
              avatarBg: "bg-butter",
              lastActive: "6 days ago",
              remarks: "Struggling with algebraic manipulation and trigonometry. Parent consultation scheduled.",
              quizResults: [
                { quizId: "tm_q1", score: 12, status: "Completed", attempts: 1, submittedOn: "16 Oct 2026" },
                { quizId: "tm_q2", score: 10, status: "Completed", attempts: 1, submittedOn: "25 Oct 2026" },
                { quizId: "tm_q3", score: null, status: "Missed", attempts: 0, submittedOn: "—" },
                { quizId: "tm_q4", score: 14, status: "Overdue", attempts: 1, submittedOn: "11 Nov 2026" }
              ],
              noteActivity: [
                { noteId: "tm_n1", read: true, timeSpent: "6 mins", lastOpened: "16 Oct 2026" },
                { noteId: "tm_n2", read: false, timeSpent: "0 mins", lastOpened: "Never" },
                { noteId: "tm_n3", read: false, timeSpent: "0 mins", lastOpened: "Never" },
                { noteId: "tm_n4", read: false, timeSpent: "0 mins", lastOpened: "Never" }
              ]
            },
            {
              id: "STU-2026-049",
              name: "Bongani Zulu",
              initials: "BZ",
              avatarBg: "bg-mint",
              lastActive: "12 hours ago",
              remarks: "Solid understanding of functions and graphs. Can push for an A symbol with extra exam paper practice.",
              quizResults: [
                { quizId: "tm_q1", score: 22, status: "Completed", attempts: 1, submittedOn: "13 Oct 2026" },
                { quizId: "tm_q2", score: 18, status: "Completed", attempts: 1, submittedOn: "21 Oct 2026" },
                { quizId: "tm_q3", score: 29, status: "Completed", attempts: 1, submittedOn: "30 Oct 2026" },
                { quizId: "tm_q4", score: 27, status: "Completed", attempts: 1, submittedOn: "07 Nov 2026" }
              ],
              noteActivity: [
                { noteId: "tm_n1", read: true, timeSpent: "15 mins", lastOpened: "12 Oct 2026" },
                { noteId: "tm_n2", read: true, timeSpent: "14 mins", lastOpened: "21 Oct 2026" },
                { noteId: "tm_n3", read: true, timeSpent: "22 mins", lastOpened: "29 Oct 2026" },
                { noteId: "tm_n4", read: true, timeSpent: "27 mins", lastOpened: "13 Nov 2026" }
              ]
            }
          ]
        },
        {
          id: "mlit11",
          code: "FML-307P",
          name: "Mathematical Literacy",
          grade: "Grade 11",
          accent: "bg-mint",
          description: "Finance (Simple & Compound Interest), Income/Expenditure Statements & Data Handling",
          quizzes: [
            { id: "ml_q1", title: "Quiz 1: Income, Expenditure & Budget Variance", dateSet: "10 Oct 2026", totalMarks: 30, topic: "Finance" },
            { id: "ml_q2", title: "Quiz 2: Simple vs Compound Interest Timelines", dateSet: "19 Oct 2026", totalMarks: 35, topic: "Banking & Loans" },
            { id: "ml_q3", title: "Quiz 3: Five-Number Summary & Box-and-Whisker", dateSet: "28 Oct 2026", totalMarks: 25, topic: "Data Handling" },
            { id: "ml_q4", title: "Quiz 4: Cumulative Frequency & Ogive Curves", dateSet: "05 Nov 2026", totalMarks: 30, topic: "Statistics" }
          ],
          notes: [
            { id: "ml_n1", title: "Notes: Club Income & Expenditure Statements", topic: "Finance", dateUploaded: "06 Oct 2026", avgTimeMins: 16 },
            { id: "ml_n2", title: "Step-by-Step Guide: Compound Interest Quarterly", topic: "Banking & Loans", dateUploaded: "15 Oct 2026", avgTimeMins: 21 },
            { id: "ml_n3", title: "Data Handling Cheat Sheet: Quartiles, IQR & Fences", topic: "Data Handling", dateUploaded: "24 Oct 2026", avgTimeMins: 19 },
            { id: "ml_n4", title: "Exam Practice: Drawing Less-Than Ogive Curves", topic: "Statistics", dateUploaded: "01 Nov 2026", avgTimeMins: 24 }
          ],
          students: [
            {
              id: "STU-2026-101",
              name: "Precious Mabaso",
              initials: "PM",
              avatarBg: "bg-mint",
              lastActive: "1 hour ago",
              remarks: "Precious excels in financial calculations and budget variance tables. Consistent 90%+ performance.",
              quizResults: [
                { quizId: "ml_q1", score: 28, status: "Completed", attempts: 1, submittedOn: "11 Oct 2026" },
                { quizId: "ml_q2", score: 32, status: "Completed", attempts: 1, submittedOn: "20 Oct 2026" },
                { quizId: "ml_q3", score: 24, status: "Completed", attempts: 1, submittedOn: "28 Oct 2026" },
                { quizId: "ml_q4", score: 27, status: "Completed", attempts: 1, submittedOn: "06 Nov 2026" }
              ],
              noteActivity: [
                { noteId: "ml_n1", read: true, timeSpent: "18 mins", lastOpened: "10 Oct 2026" },
                { noteId: "ml_n2", read: true, timeSpent: "24 mins", lastOpened: "18 Oct 2026" },
                { noteId: "ml_n3", read: true, timeSpent: "20 mins", lastOpened: "27 Oct 2026" },
                { noteId: "ml_n4", read: true, timeSpent: "28 mins", lastOpened: "14 Nov 2026" }
              ]
            },
            {
              id: "STU-2026-104",
              name: "Kabelo Modise",
              initials: "KM",
              avatarBg: "bg-butter",
              lastActive: "6 hours ago",
              remarks: "Very good understanding of data handling and box-and-whisker plots.",
              quizResults: [
                { quizId: "ml_q1", score: 24, status: "Completed", attempts: 1, submittedOn: "11 Oct 2026" },
                { quizId: "ml_q2", score: 26, status: "Completed", attempts: 1, submittedOn: "21 Oct 2026" },
                { quizId: "ml_q3", score: 22, status: "Completed", attempts: 1, submittedOn: "29 Oct 2026" },
                { quizId: "ml_q4", score: 23, status: "Completed", attempts: 1, submittedOn: "07 Nov 2026" }
              ],
              noteActivity: [
                { noteId: "ml_n1", read: true, timeSpent: "15 mins", lastOpened: "10 Oct 2026" },
                { noteId: "ml_n2", read: true, timeSpent: "19 mins", lastOpened: "20 Oct 2026" },
                { noteId: "ml_n3", read: true, timeSpent: "17 mins", lastOpened: "28 Oct 2026" },
                { noteId: "ml_n4", read: true, timeSpent: "21 mins", lastOpened: "12 Nov 2026" }
              ]
            },
            {
              id: "STU-2026-108",
              name: "Naledi Phiri",
              initials: "NP",
              avatarBg: "bg-sky",
              lastActive: "2 days ago",
              remarks: "Good progress in statistics, needs to review compound interest compounding periods (quarterly vs annually).",
              quizResults: [
                { quizId: "ml_q1", score: 21, status: "Completed", attempts: 1, submittedOn: "12 Oct 2026" },
                { quizId: "ml_q2", score: 18, status: "Completed", attempts: 2, submittedOn: "22 Oct 2026" },
                { quizId: "ml_q3", score: 19, status: "Completed", attempts: 1, submittedOn: "30 Oct 2026" },
                { quizId: "ml_q4", score: 22, status: "Completed", attempts: 1, submittedOn: "08 Nov 2026" }
              ],
              noteActivity: [
                { noteId: "ml_n1", read: true, timeSpent: "14 mins", lastOpened: "11 Oct 2026" },
                { noteId: "ml_n2", read: false, timeSpent: "0 mins", lastOpened: "Never" },
                { noteId: "ml_n3", read: true, timeSpent: "18 mins", lastOpened: "29 Oct 2026" },
                { noteId: "ml_n4", read: true, timeSpent: "20 mins", lastOpened: "07 Nov 2026" }
              ]
            },
            {
              id: "STU-2026-112",
              name: "Sipho Mahlangu",
              initials: "SM",
              avatarBg: "bg-coral",
              lastActive: "5 days ago",
              remarks: "Incomplete quizzes and low note engagement. Must complete Quiz 3 and review financial literacy notes.",
              quizResults: [
                { quizId: "ml_q1", score: 14, status: "Completed", attempts: 1, submittedOn: "14 Oct 2026" },
                { quizId: "ml_q2", score: 13, status: "Completed", attempts: 1, submittedOn: "25 Oct 2026" },
                { quizId: "ml_q3", score: null, status: "Missed", attempts: 0, submittedOn: "—" },
                { quizId: "ml_q4", score: 15, status: "Overdue", attempts: 1, submittedOn: "10 Nov 2026" }
              ],
              noteActivity: [
                { noteId: "ml_n1", read: true, timeSpent: "8 mins", lastOpened: "14 Oct 2026" },
                { noteId: "ml_n2", read: false, timeSpent: "0 mins", lastOpened: "Never" },
                { noteId: "ml_n3", read: false, timeSpent: "0 mins", lastOpened: "Never" },
                { noteId: "ml_n4", read: false, timeSpent: "0 mins", lastOpened: "Never" }
              ]
            },
            {
              id: "STU-2026-115",
              name: "Tshepo Radebe",
              initials: "TR",
              avatarBg: "bg-lavender",
              lastActive: "1 day ago",
              remarks: "Hardworking learner who actively engages with all study guides.",
              quizResults: [
                { quizId: "ml_q1", score: 25, status: "Completed", attempts: 1, submittedOn: "10 Oct 2026" },
                { quizId: "ml_q2", score: 27, status: "Completed", attempts: 1, submittedOn: "20 Oct 2026" },
                { quizId: "ml_q3", score: 20, status: "Completed", attempts: 1, submittedOn: "28 Oct 2026" },
                { quizId: "ml_q4", score: 25, status: "Completed", attempts: 1, submittedOn: "05 Nov 2026" }
              ],
              noteActivity: [
                { noteId: "ml_n1", read: true, timeSpent: "17 mins", lastOpened: "09 Oct 2026" },
                { noteId: "ml_n2", read: true, timeSpent: "22 mins", lastOpened: "19 Oct 2026" },
                { noteId: "ml_n3", read: true, timeSpent: "18 mins", lastOpened: "27 Oct 2026" },
                { noteId: "ml_n4", read: true, timeSpent: "23 mins", lastOpened: "04 Nov 2026" }
              ]
            },
            {
              id: "STU-2026-119",
              name: "Ayanda Buthelezi",
              initials: "AB",
              avatarBg: "bg-sky",
              lastActive: "4 hours ago",
              remarks: "Consistent mid-70s achiever. Very neat ogive curve construction.",
              quizResults: [
                { quizId: "ml_q1", score: 22, status: "Completed", attempts: 1, submittedOn: "11 Oct 2026" },
                { quizId: "ml_q2", score: 25, status: "Completed", attempts: 1, submittedOn: "20 Oct 2026" },
                { quizId: "ml_q3", score: 19, status: "Completed", attempts: 1, submittedOn: "29 Oct 2026" },
                { quizId: "ml_q4", score: 24, status: "Completed", attempts: 1, submittedOn: "06 Nov 2026" }
              ],
              noteActivity: [
                { noteId: "ml_n1", read: true, timeSpent: "15 mins", lastOpened: "11 Oct 2026" },
                { noteId: "ml_n2", read: true, timeSpent: "20 mins", lastOpened: "19 Oct 2026" },
                { noteId: "ml_n3", read: true, timeSpent: "16 mins", lastOpened: "28 Oct 2026" },
                { noteId: "ml_n4", read: true, timeSpent: "22 mins", lastOpened: "06 Nov 2026" }
              ]
            },
            {
              id: "STU-2026-122",
              name: "Lindiwe Gumede",
              initials: "LG",
              avatarBg: "bg-mint",
              lastActive: "3 hours ago",
              remarks: "Outstanding comprehension of real-world financial scenarios.",
              quizResults: [
                { quizId: "ml_q1", score: 27, status: "Completed", attempts: 1, submittedOn: "10 Oct 2026" },
                { quizId: "ml_q2", score: 30, status: "Completed", attempts: 1, submittedOn: "19 Oct 2026" },
                { quizId: "ml_q3", score: 23, status: "Completed", attempts: 1, submittedOn: "28 Oct 2026" },
                { quizId: "ml_q4", score: 28, status: "Completed", attempts: 1, submittedOn: "05 Nov 2026" }
              ],
              noteActivity: [
                { noteId: "ml_n1", read: true, timeSpent: "19 mins", lastOpened: "09 Oct 2026" },
                { noteId: "ml_n2", read: true, timeSpent: "25 mins", lastOpened: "18 Oct 2026" },
                { noteId: "ml_n3", read: true, timeSpent: "21 mins", lastOpened: "27 Oct 2026" },
                { noteId: "ml_n4", read: true, timeSpent: "26 mins", lastOpened: "14 Nov 2026" }
              ]
            },
            {
              id: "STU-2026-126",
              name: "Mpho Sekgobela",
              initials: "MS",
              avatarBg: "bg-butter",
              lastActive: "2 days ago",
              remarks: "Satisfactory work, encouraged to complete Quiz 4 revision questions.",
              quizResults: [
                { quizId: "ml_q1", score: 18, status: "Completed", attempts: 1, submittedOn: "12 Oct 2026" },
                { quizId: "ml_q2", score: 21, status: "Completed", attempts: 1, submittedOn: "21 Oct 2026" },
                { quizId: "ml_q3", score: 16, status: "Completed", attempts: 2, submittedOn: "30 Oct 2026" },
                { quizId: "ml_q4", score: 18, status: "Completed", attempts: 1, submittedOn: "07 Nov 2026" }
              ],
              noteActivity: [
                { noteId: "ml_n1", read: true, timeSpent: "12 mins", lastOpened: "12 Oct 2026" },
                { noteId: "ml_n2", read: true, timeSpent: "15 mins", lastOpened: "21 Oct 2026" },
                { noteId: "ml_n3", read: false, timeSpent: "0 mins", lastOpened: "Never" },
                { noteId: "ml_n4", read: true, timeSpent: "14 mins", lastOpened: "07 Nov 2026" }
              ]
            }
          ]
        },
        {
          id: "cat11",
          code: "CAT-11",
          name: "Computer Applications Tech",
          grade: "Grade 11",
          accent: "bg-sky",
          description: "Spreadsheets (Excel Functions), Relational Databases (Access) & HTML/Web Design",
          quizzes: [
            { id: "cat_q1", title: "Quiz 1: Advanced Excel IF, VLOOKUP & CountIF", dateSet: "11 Oct 2026", totalMarks: 40, topic: "Spreadsheets" },
            { id: "cat_q2", title: "Quiz 2: Database Queries, Forms & Primary Keys", dateSet: "21 Oct 2026", totalMarks: 30, topic: "Databases" },
            { id: "cat_q3", title: "Quiz 3: HTML5 Semantic Structure & Tables", dateSet: "30 Oct 2026", totalMarks: 35, topic: "Web Development" },
            { id: "cat_q4", title: "Quiz 4: Network Topologies & Cyber Security", dateSet: "07 Nov 2026", totalMarks: 25, topic: "Systems Tech" }
          ],
          notes: [
            { id: "cat_n1", title: "Lab Manual: Nested IF & Lookup Functions in Excel", topic: "Spreadsheets", dateUploaded: "07 Oct 2026", avgTimeMins: 26 },
            { id: "cat_n2", title: "Database Design Guide: Data Types & Validation Rules", topic: "Databases", dateUploaded: "17 Oct 2026", avgTimeMins: 20 },
            { id: "cat_n3", title: "HTML Tag Reference & Table Attributes Handbook", topic: "Web Development", dateUploaded: "26 Oct 2026", avgTimeMins: 22 },
            { id: "cat_n4", title: "Theory Summary: Cloud Computing & Malware Protection", topic: "Systems Tech", dateUploaded: "03 Nov 2026", avgTimeMins: 15 }
          ],
          students: [
            {
              id: "STU-2026-014",
              name: "Sizwe Nkosi",
              initials: "SN",
              avatarBg: "bg-sky",
              lastActive: "1 hour ago",
              remarks: "Sizwe excels in practical HTML and database query design. Very high engagement with lab manuals.",
              quizResults: [
                { quizId: "cat_q1", score: 36, status: "Completed", attempts: 1, submittedOn: "11 Oct 2026" },
                { quizId: "cat_q2", score: 28, status: "Completed", attempts: 1, submittedOn: "21 Oct 2026" },
                { quizId: "cat_q3", score: 34, status: "Completed", attempts: 1, submittedOn: "30 Oct 2026" },
                { quizId: "cat_q4", score: 23, status: "Completed", attempts: 1, submittedOn: "07 Nov 2026" }
              ],
              noteActivity: [
                { noteId: "cat_n1", read: true, timeSpent: "30 mins", lastOpened: "10 Oct 2026" },
                { noteId: "cat_n2", read: true, timeSpent: "22 mins", lastOpened: "20 Oct 2026" },
                { noteId: "cat_n3", read: true, timeSpent: "28 mins", lastOpened: "29 Oct 2026" },
                { noteId: "cat_n4", read: true, timeSpent: "18 mins", lastOpened: "07 Nov 2026" }
              ]
            },
            {
              id: "STU-2026-205",
              name: "Oratile Mokoena",
              initials: "OM",
              avatarBg: "bg-mint",
              lastActive: "3 hours ago",
              remarks: "Strong spreadsheet modeling skills. Ranked #1 in Excel VLOOKUP practical quiz.",
              quizResults: [
                { quizId: "cat_q1", score: 39, status: "Completed", attempts: 1, submittedOn: "11 Oct 2026" },
                { quizId: "cat_q2", score: 27, status: "Completed", attempts: 1, submittedOn: "22 Oct 2026" },
                { quizId: "cat_q3", score: 31, status: "Completed", attempts: 1, submittedOn: "31 Oct 2026" },
                { quizId: "cat_q4", score: 24, status: "Completed", attempts: 1, submittedOn: "07 Nov 2026" }
              ],
              noteActivity: [
                { noteId: "cat_n1", read: true, timeSpent: "32 mins", lastOpened: "10 Oct 2026" },
                { noteId: "cat_n2", read: true, timeSpent: "21 mins", lastOpened: "21 Oct 2026" },
                { noteId: "cat_n3", read: true, timeSpent: "24 mins", lastOpened: "30 Oct 2026" },
                { noteId: "cat_n4", read: true, timeSpent: "17 mins", lastOpened: "06 Nov 2026" }
              ]
            },
            {
              id: "STU-2026-209",
              name: "Rethabile Tau",
              initials: "RT",
              avatarBg: "bg-butter",
              lastActive: "1 day ago",
              remarks: "Solid theory marks; needs to practice database criteria expressions.",
              quizResults: [
                { quizId: "cat_q1", score: 28, status: "Completed", attempts: 1, submittedOn: "12 Oct 2026" },
                { quizId: "cat_q2", score: 19, status: "Completed", attempts: 2, submittedOn: "23 Oct 2026" },
                { quizId: "cat_q3", score: 26, status: "Completed", attempts: 1, submittedOn: "01 Nov 2026" },
                { quizId: "cat_q4", score: 20, status: "Completed", attempts: 1, submittedOn: "08 Nov 2026" }
              ],
              noteActivity: [
                { noteId: "cat_n1", read: true, timeSpent: "20 mins", lastOpened: "12 Oct 2026" },
                { noteId: "cat_n2", read: false, timeSpent: "0 mins", lastOpened: "Never" },
                { noteId: "cat_n3", read: true, timeSpent: "19 mins", lastOpened: "31 Oct 2026" },
                { noteId: "cat_n4", read: true, timeSpent: "14 mins", lastOpened: "08 Nov 2026" }
              ]
            },
            {
              id: "STU-2026-214",
              name: "Dumisani Khumalo",
              initials: "DK",
              avatarBg: "bg-coral",
              lastActive: "7 days ago",
              remarks: "Missed HTML and Systems Tech quizzes. Needs immediate catch-up session in the computer lab.",
              quizResults: [
                { quizId: "cat_q1", score: 18, status: "Completed", attempts: 1, submittedOn: "14 Oct 2026" },
                { quizId: "cat_q2", score: 14, status: "Completed", attempts: 1, submittedOn: "25 Oct 2026" },
                { quizId: "cat_q3", score: null, status: "Missed", attempts: 0, submittedOn: "—" },
                { quizId: "cat_q4", score: null, status: "Missed", attempts: 0, submittedOn: "—" }
              ],
              noteActivity: [
                { noteId: "cat_n1", read: true, timeSpent: "11 mins", lastOpened: "14 Oct 2026" },
                { noteId: "cat_n2", read: false, timeSpent: "0 mins", lastOpened: "Never" },
                { noteId: "cat_n3", read: false, timeSpent: "0 mins", lastOpened: "Never" },
                { noteId: "cat_n4", read: false, timeSpent: "0 mins", lastOpened: "Never" }
              ]
            },
            {
              id: "STU-2026-218",
              name: "Zinhle Mthembu",
              initials: "ZM",
              avatarBg: "bg-lavender",
              lastActive: "5 hours ago",
              remarks: "Great creativity in HTML web structure and thorough understanding of network security.",
              quizResults: [
                { quizId: "cat_q1", score: 31, status: "Completed", attempts: 1, submittedOn: "11 Oct 2026" },
                { quizId: "cat_q2", score: 25, status: "Completed", attempts: 1, submittedOn: "21 Oct 2026" },
                { quizId: "cat_q3", score: 32, status: "Completed", attempts: 1, submittedOn: "30 Oct 2026" },
                { quizId: "cat_q4", score: 22, status: "Completed", attempts: 1, submittedOn: "07 Nov 2026" }
              ],
              noteActivity: [
                { noteId: "cat_n1", read: true, timeSpent: "24 mins", lastOpened: "10 Oct 2026" },
                { noteId: "cat_n2", read: true, timeSpent: "19 mins", lastOpened: "20 Oct 2026" },
                { noteId: "cat_n3", read: true, timeSpent: "25 mins", lastOpened: "29 Oct 2026" },
                { noteId: "cat_n4", read: true, timeSpent: "16 mins", lastOpened: "07 Nov 2026" }
              ]
            },
            {
              id: "STU-2026-221",
              name: "Sibusiso Nkomo",
              initials: "SN",
              avatarBg: "bg-sky",
              lastActive: "1 day ago",
              remarks: "Consistent performer across both practical and theory modules.",
              quizResults: [
                { quizId: "cat_q1", score: 30, status: "Completed", attempts: 1, submittedOn: "12 Oct 2026" },
                { quizId: "cat_q2", score: 23, status: "Completed", attempts: 1, submittedOn: "22 Oct 2026" },
                { quizId: "cat_q3", score: 27, status: "Completed", attempts: 1, submittedOn: "31 Oct 2026" },
                { quizId: "cat_q4", score: 19, status: "Completed", attempts: 1, submittedOn: "08 Nov 2026" }
              ],
              noteActivity: [
                { noteId: "cat_n1", read: true, timeSpent: "21 mins", lastOpened: "11 Oct 2026" },
                { noteId: "cat_n2", read: true, timeSpent: "18 mins", lastOpened: "21 Oct 2026" },
                { noteId: "cat_n3", read: true, timeSpent: "20 mins", lastOpened: "30 Oct 2026" },
                { noteId: "cat_n4", read: false, timeSpent: "0 mins", lastOpened: "Never" }
              ]
            }
          ]
        }
      ]
    };

    // ==========================================
    // 2. APPLICATION STATE
    // ==========================================
    let selectedSubjectId = educatorData.subjects[0].id;
    let activeWorkspaceTab = "students"; // 'students' | 'content'
    let performanceFilter = "all"; // 'all' | 'excelling' | 'ontrack' | 'attention'
    let activeModalStudentId = null;

    // ==========================================
    // 3. HELPER CALCULATION FUNCTIONS
    // ==========================================
    function getSelectedSubject() {
      return educatorData.subjects.find(s => s.id === selectedSubjectId);
    }

    function getLearnerPresence(student) {
      if (!student.userId) {
        return {
          state: "unlinked",
          label: "Demo · unlinked",
          lastActive: "No linked account"
        };
      }

      const presence = educatorData.learnerPresence[String(student.userId)];
      if (presence) {
        return {
          state: presence.state,
          label: presence.label,
          lastActive: presence.last_active || "No session recorded"
        };
      }

      return {
        state: "offline",
        label: "Offline",
        lastActive: "No active session"
      };
    }

    function renderPresenceBadge(student) {
      const presence = getLearnerPresence(student);
      const badgeClass = {
        online: "bg-mint",
        offline: "bg-coral",
        unlinked: "bg-butter",
        unavailable: "bg-lavender"
      }[presence.state] || "bg-lavender";

      return `
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold border-2 border-black rounded-full ${badgeClass}"
          aria-label="Presence: ${presence.label}">
          <span class="w-2 h-2 rounded-full bg-black" aria-hidden="true"></span>
          ${presence.label}
        </span>
      `;
    }

    function calculateStudentMetrics(student, subject) {
      const totalQuizzes = subject.quizzes.length;
      let completedCount = 0;
      let totalPercentageSum = 0;

      student.quizResults.forEach(res => {
        const quizMeta = subject.quizzes.find(q => q.id === res.quizId);
        if (res.score !== null && quizMeta) {
          completedCount++;
          totalPercentageSum += (res.score / quizMeta.totalMarks) * 100;
        }
      });

      const avgQuizPercent = completedCount > 0 ? Math.round(totalPercentageSum / completedCount) : 0;

      const totalNotes = subject.notes.length;
      const notesReadCount = student.noteActivity.filter(n => n.read).length;
      const notesEngagementPercent = totalNotes > 0 ? Math.round((notesReadCount / totalNotes) * 100) : 0;

      let statusLabel = "On Track";
      let statusBadgeClass = "bg-sky";
      let category = "ontrack";

      if (avgQuizPercent >= 75 && completedCount === totalQuizzes) {
        statusLabel = "Excelling";
        statusBadgeClass = "bg-mint";
        category = "excelling";
      } else if (avgQuizPercent < 50 || completedCount < totalQuizzes - 1) {
        statusLabel = "Needs Attention";
        statusBadgeClass = "bg-coral";
        category = "attention";
      }

      return {
        completedCount,
        totalQuizzes,
        avgQuizPercent,
        notesReadCount,
        totalNotes,
        notesEngagementPercent,
        statusLabel,
        statusBadgeClass,
        category
      };
    }

    function calculateSubjectSummary(subject) {
      let totalStudents = subject.students.length;
      let quizAvgSum = 0;
      let notesRateSum = 0;

      subject.students.forEach(st => {
        const m = calculateStudentMetrics(st, subject);
        quizAvgSum += m.avgQuizPercent;
        notesRateSum += m.notesEngagementPercent;
      });

      const classQuizAvg = totalStudents > 0 ? (quizAvgSum / totalStudents).toFixed(1) : "0.0";
      const classNotesRate = totalStudents > 0 ? Math.round(notesRateSum / totalStudents) : 0;

      return {
        totalStudents,
        classQuizAvg,
        classNotesRate
      };
    }

    // ==========================================
    // 4. RENDER FUNCTIONS
    // ==========================================
    function renderKPIs() {
      const subject = getSelectedSubject();
      const summary = calculateSubjectSummary(subject);
      const allStudentsCount = educatorData.subjects.reduce((acc, s) => acc + s.students.length, 0);

      document.getElementById("kpiTotalSubjects").textContent = educatorData.subjects.length;
      document.getElementById("kpiTotalLearners").textContent = allStudentsCount;
      document.getElementById("kpiAvgQuiz").textContent = `${summary.classQuizAvg}%`;
      document.getElementById("kpiAvgQuizSub").textContent = `${subject.code} class average`;
      document.getElementById("kpiNotesRate").textContent = `${summary.classNotesRate}%`;
      document.getElementById("kpiNotesSub").textContent = `${subject.code} notes read rate`;
    }

    function renderSubjectCards() {
      const container = document.getElementById("subjectsGrid");
      container.innerHTML = "";

      educatorData.subjects.forEach(subject => {
        const isSelected = subject.id === selectedSubjectId;
        const summary = calculateSubjectSummary(subject);

        const card = document.createElement("button");
        card.type = "button";
        card.onclick = () => selectSubject(subject.id);
        card.className = `brutal-card brutal-card-interactive text-left rounded-2xl p-5 flex flex-col justify-between cursor-pointer relative ${
          isSelected ? `${subject.accent} ring-2 ring-black` : "bg-white hover:bg-cream"
        }`;

        card.innerHTML = `
          <div>
            <div class="flex items-center justify-between gap-2">
              <span class="px-2.5 py-0.5 text-xs font-extrabold uppercase bg-black text-white rounded-md">
                ${subject.code}
              </span>
              ${
                isSelected
                  ? `<span class="px-2.5 py-0.5 text-xs font-extrabold uppercase bg-white border-2 border-black rounded-full flex items-center gap-1">
                      <span class="w-2 h-2 rounded-full bg-emerald-600"></span> Selected
                    </span>`
                  : `<span class="px-2.5 py-0.5 text-xs font-bold bg-cream border-2 border-black rounded-full">
                      ${subject.grade}
                    </span>`
              }
            </div>

            <h3 class="font-display font-extrabold text-xl mt-3 leading-snug">${subject.name}</h3>
            <p class="text-xs text-neutral-700 font-medium mt-1 line-clamp-2">${subject.description}</p>
          </div>

          <div class="mt-5 pt-3.5 border-t-2 border-black/20 grid grid-cols-3 gap-2 text-center">
            <div class="bg-white/80 border border-black rounded-xl p-2">
              <p class="text-[11px] font-bold uppercase text-neutral-500">Students</p>
              <p class="font-display font-extrabold text-base">${subject.students.length}</p>
            </div>
            <div class="bg-white/80 border border-black rounded-xl p-2">
              <p class="text-[11px] font-bold uppercase text-neutral-500">Quizzes</p>
              <p class="font-display font-extrabold text-base">${subject.quizzes.length}</p>
            </div>
            <div class="bg-white/80 border border-black rounded-xl p-2">
              <p class="text-[11px] font-bold uppercase text-neutral-500">Notes</p>
              <p class="font-display font-extrabold text-base">${subject.notes.length}</p>
            </div>
          </div>
        `;

        container.appendChild(card);
      });
    }

    function updateWorkspaceHeader() {
      const subject = getSelectedSubject();
      document.getElementById("activeSubjectCode").textContent = subject.code;
      document.getElementById("activeSubjectGrade").textContent = subject.grade;
      document.getElementById("activeSubjectName").textContent = subject.name;
      document.getElementById("activeSubjectDesc").textContent = `${subject.description} • Click any student to generate their subject report`;
      document.getElementById("tabStudentCount").textContent = subject.students.length;
      document.getElementById("tabResourceCount").textContent = subject.quizzes.length + subject.notes.length;
    }

    function renderStudentsTable() {
      const subject = getSelectedSubject();
      const searchQuery = document.getElementById("studentSearchInput").value.trim().toLowerCase();
      const tbody = document.getElementById("studentsTableBody");
      tbody.innerHTML = "";

      const filteredStudents = subject.students.filter(student => {
        const metrics = calculateStudentMetrics(student, subject);
        const matchesSearch =
          student.name.toLowerCase().includes(searchQuery) ||
          student.id.toLowerCase().includes(searchQuery);
        const matchesFilter =
          performanceFilter === "all" || metrics.category === performanceFilter;
        return matchesSearch && matchesFilter;
      });

      if (filteredStudents.length === 0) {
        tbody.innerHTML = `
          <tr>
            <td colspan="8" class="py-10 px-4 text-center text-neutral-500 font-medium">
              No students match your current search or performance filter in <strong>${subject.name}</strong>.
            </td>
          </tr>
        `;
        return;
      }

      filteredStudents.forEach(student => {
        const m = calculateStudentMetrics(student, subject);
        const tr = document.createElement("tr");
        tr.className = "hover:bg-cream/90 transition-colors group";

        tr.innerHTML = `
          <td class="py-3.5 px-4">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl ${student.avatarBg} border-2 border-black flex items-center justify-center font-display font-extrabold text-sm shrink-0">
                ${student.initials}
              </div>
              <div>
                <button onclick="openStudentReport('${student.id}')" class="font-display font-bold text-sm sm:text-base hover:underline text-left cursor-pointer">
                  ${student.name}
                </button>
                <p class="text-xs text-neutral-500 font-medium">${student.id}</p>
              </div>
            </div>
          </td>

          <td class="py-3.5 px-4">
            <span class="font-display font-bold text-sm">${m.completedCount} / ${m.totalQuizzes}</span>
            <span class="text-xs text-neutral-500 block">quizzes done</span>
          </td>

          <td class="py-3.5 px-4">
            <div class="flex items-center gap-2">
              <span class="font-display font-extrabold text-base">${m.avgQuizPercent}%</span>
              <div class="w-16 h-2.5 bg-neutral-200 border border-black rounded-full overflow-hidden hidden sm:block">
                <div class="h-full ${m.avgQuizPercent >= 75 ? 'bg-emerald-400' : m.avgQuizPercent >= 50 ? 'bg-sky-400' : 'bg-rose-400'}" style="width: ${m.avgQuizPercent}%"></div>
              </div>
            </div>
          </td>

          <td class="py-3.5 px-4">
            <span class="font-display font-bold text-sm">${m.notesReadCount} / ${m.totalNotes}</span>
            <span class="text-xs text-neutral-500 block">${m.notesEngagementPercent}% read</span>
          </td>

          <td class="py-3.5 px-4">
            <span class="px-2.5 py-1 text-xs font-bold border-2 border-black rounded-full ${m.statusBadgeClass} whitespace-nowrap">
              ${m.statusLabel}
            </span>
          </td>

          <td class="py-3.5 px-4 whitespace-nowrap">
            ${renderPresenceBadge(student)}
          </td>

          <td class="py-3.5 px-4 text-xs font-medium text-neutral-600 whitespace-nowrap">
            ${getLearnerPresence(student).lastActive}
          </td>

          <td class="py-3.5 px-4 text-right whitespace-nowrap">
            <button onclick="openStudentReport('${student.id}')" class="brutal-btn bg-butter hover:bg-amber-300 px-3.5 py-2 rounded-xl font-display font-bold text-xs cursor-pointer inline-flex items-center gap-1.5">
              <span>View Student Report</span>
              <span>→</span>
            </button>
          </td>
        `;

        tbody.appendChild(tr);
      });
    }

    function renderContentEngagementReport() {
      const subject = getSelectedSubject();
      const quizzesGrid = document.getElementById("quizzesReportGrid");
      const notesGrid = document.getElementById("notesReportGrid");

      document.getElementById("quizzesSummaryBadge").textContent = `${subject.quizzes.length} Quizzes Set`;
      document.getElementById("notesSummaryBadge").textContent = `${subject.notes.length} Study Notes Uploaded`;

      quizzesGrid.innerHTML = "";
      subject.quizzes.forEach(quiz => {
        let completedStudents = 0;
        let scorePercentages = [];

        subject.students.forEach(st => {
          const res = st.quizResults.find(r => r.quizId === quiz.id);
          if (res && res.score !== null) {
            completedStudents++;
            scorePercentages.push(Math.round((res.score / quiz.totalMarks) * 100));
          }
        });

        const completionRate = Math.round((completedStudents / subject.students.length) * 100);
        const avgScore = scorePercentages.length > 0
          ? Math.round(scorePercentages.reduce((a, b) => a + b, 0) / scorePercentages.length)
          : 0;
        const highestScore = scorePercentages.length > 0 ? Math.max(...scorePercentages) : 0;
        const lowestScore = scorePercentages.length > 0 ? Math.min(...scorePercentages) : 0;

        const card = document.createElement("div");
        card.className = "brutal-card bg-cream rounded-2xl p-4 sm:p-5 flex flex-col justify-between space-y-4";
        card.innerHTML = `
          <div>
            <div class="flex items-start justify-between gap-2">
              <span class="px-2.5 py-0.5 text-xs font-bold bg-white border-2 border-black rounded-md">
                ${quiz.topic}
              </span>
              <span class="text-xs font-semibold text-neutral-500">Set: ${quiz.dateSet} • ${quiz.totalMarks} Marks</span>
            </div>
            <h5 class="font-display font-extrabold text-base sm:text-lg mt-2">${quiz.title}</h5>
          </div>

          <div class="space-y-3">
            <!-- Completion Progress Bar -->
            <div>
              <div class="flex justify-between text-xs font-bold mb-1">
                <span>Learner Completion Rate</span>
                <span>${completedStudents}/${subject.students.length} Students (${completionRate}%)</span>
              </div>
              <div class="w-full h-3.5 bg-white border-2 border-black rounded-full overflow-hidden">
                <div class="h-full bg-mint striped-progress border-r-2 border-black" style="width: ${completionRate}%"></div>
              </div>
            </div>

            <!-- Metrics Mini Row -->
            <div class="grid grid-cols-3 gap-2 pt-1 text-center">
              <div class="bg-white border-2 border-black rounded-xl p-2">
                <p class="text-[11px] font-bold uppercase text-neutral-500">Class Avg</p>
                <p class="font-display font-extrabold text-sm sm:text-base">${avgScore}%</p>
              </div>
              <div class="bg-white border-2 border-black rounded-xl p-2">
                <p class="text-[11px] font-bold uppercase text-neutral-500">Highest</p>
                <p class="font-display font-extrabold text-sm sm:text-base text-emerald-700">${highestScore}%</p>
              </div>
              <div class="bg-white border-2 border-black rounded-xl p-2">
                <p class="text-[11px] font-bold uppercase text-neutral-500">Lowest</p>
                <p class="font-display font-extrabold text-sm sm:text-base text-rose-700">${lowestScore}%</p>
              </div>
            </div>

            <button onclick="openResourceInspector('quiz', '${quiz.id}')" class="brutal-btn w-full bg-white hover:bg-butter py-2 rounded-xl font-display font-bold text-xs cursor-pointer">
              Inspect Student Submissions (${completedStudents}/${subject.students.length})
            </button>
          </div>
        `;
        quizzesGrid.appendChild(card);
      });

      notesGrid.innerHTML = "";
      subject.notes.forEach(note => {
        const readStudents = subject.students.filter(st => {
          const act = st.noteActivity.find(a => a.noteId === note.id);
          return act && act.read;
        }).length;

        const readRate = Math.round((readStudents / subject.students.length) * 100);

        const card = document.createElement("div");
        card.className = "brutal-card bg-cream rounded-2xl p-4 sm:p-5 flex flex-col justify-between space-y-4";
        card.innerHTML = `
          <div>
            <div class="flex items-start justify-between gap-2">
              <span class="px-2.5 py-0.5 text-xs font-bold bg-sky border-2 border-black rounded-md">
                ${note.topic}
              </span>
              <span class="text-xs font-semibold text-neutral-500">Uploaded: ${note.dateUploaded}</span>
            </div>
            <h5 class="font-display font-extrabold text-base sm:text-lg mt-2">${note.title}</h5>
          </div>

          <div class="space-y-3">
            <div>
              <div class="flex justify-between text-xs font-bold mb-1">
                <span>Learner Read Engagement</span>
                <span>${readStudents}/${subject.students.length} Learners (${readRate}%)</span>
              </div>
              <div class="w-full h-3.5 bg-white border-2 border-black rounded-full overflow-hidden">
                <div class="h-full bg-sky striped-progress border-r-2 border-black" style="width: ${readRate}%"></div>
              </div>
            </div>

            <div class="flex items-center justify-between bg-white border-2 border-black rounded-xl px-3.5 py-2 text-xs font-bold">
              <span>Avg. Reading Time: <strong class="font-display text-sm">${note.avgTimeMins} mins</strong></span>
              <button onclick="openResourceInspector('note', '${note.id}')" class="underline hover:text-blue-700 cursor-pointer">
                See Who Read →
              </button>
            </div>
          </div>
        `;
        notesGrid.appendChild(card);
      });
    }

    // ==========================================
    // 5. INTERACTION HANDLERS
    // ==========================================
    function selectSubject(subjectId) {
      selectedSubjectId = subjectId;
      performanceFilter = "all";
      document.getElementById("studentSearchInput").value = "";
      updateFilterPillsUI();
      renderKPIs();
      renderSubjectCards();
      updateWorkspaceHeader();
      renderStudentsTable();
      renderContentEngagementReport();
    }

    function switchWorkspaceTab(tabName) {
      activeWorkspaceTab = tabName;
      const studentsBtn = document.getElementById("tabStudentsBtn");
      const contentBtn = document.getElementById("tabContentBtn");
      const studentsPanel = document.getElementById("studentsTabPanel");
      const contentPanel = document.getElementById("contentTabPanel");

      if (tabName === "students") {
        studentsBtn.className = "px-4 py-2 rounded-xl font-display font-bold text-xs sm:text-sm transition-all cursor-pointer flex items-center gap-2 bg-butter border-2 border-black shadow-brutal-sm";
        contentBtn.className = "px-4 py-2 rounded-xl font-display font-bold text-xs sm:text-sm transition-all cursor-pointer flex items-center gap-2 text-neutral-700 hover:bg-cream border-2 border-transparent";
        studentsPanel.classList.remove("hidden");
        contentPanel.classList.add("hidden");
      } else {
        contentBtn.className = "px-4 py-2 rounded-xl font-display font-bold text-xs sm:text-sm transition-all cursor-pointer flex items-center gap-2 bg-butter border-2 border-black shadow-brutal-sm";
        studentsBtn.className = "px-4 py-2 rounded-xl font-display font-bold text-xs sm:text-sm transition-all cursor-pointer flex items-center gap-2 text-neutral-700 hover:bg-cream border-2 border-transparent";
        contentPanel.classList.remove("hidden");
        studentsPanel.classList.add("hidden");
      }
    }

    function setPerformanceFilter(filterValue) {
      performanceFilter = filterValue;
      updateFilterPillsUI();
      renderStudentsTable();
    }

    function updateFilterPillsUI() {
      document.querySelectorAll(".filter-pill").forEach(btn => {
        const val = btn.getAttribute("data-filter");
        if (val === performanceFilter) {
          btn.classList.add("ring-2", "ring-offset-1", "ring-black", "shadow-brutal-sm");
        } else {
          btn.classList.remove("ring-2", "ring-offset-1", "ring-black", "shadow-brutal-sm");
        }
      });
    }

    // ==========================================
    // 6. INDIVIDUAL STUDENT SUBJECT REPORT MODAL
    // ==========================================
    function openStudentReport(studentId) {
      const subject = getSelectedSubject();
      const student = subject.students.find(s => s.id === studentId);
      if (!student) return;

      const presence = getLearnerPresence(student);
      activeModalStudentId = studentId;
      const metrics = calculateStudentMetrics(student, subject);
      const termValue = document.getElementById("termSelector").selectedOptions[0].textContent.trim();
      document.getElementById("reportModalTerm").textContent = termValue === "All terms" ? "Term 4 • 2026" : termValue.replace(" (Current)", "");

      // Build Quiz Rows for this Student in this Subject
      const quizRowsHTML = subject.quizzes.map(quiz => {
        const res = student.quizResults.find(r => r.quizId === quiz.id);
        // Calculate class avg for comparison
        let classScores = [];
        subject.students.forEach(st => {
          const r = st.quizResults.find(item => item.quizId === quiz.id);
          if (r && r.score !== null) {
            classScores.push((r.score / quiz.totalMarks) * 100);
          }
        });
        const classAvg = classScores.length > 0
          ? Math.round(classScores.reduce((a, b) => a + b, 0) / classScores.length)
          : 0;

        const studentPct = res && res.score !== null ? Math.round((res.score / quiz.totalMarks) * 100) : null;
        let statusBadge = `<span class="px-2 py-0.5 text-xs font-bold bg-mint border border-black rounded-md">Completed</span>`;
        if (res.status === "Missed") {
          statusBadge = `<span class="px-2 py-0.5 text-xs font-bold bg-coral border border-black rounded-md">Missed</span>`;
        } else if (res.status === "Overdue") {
          statusBadge = `<span class="px-2 py-0.5 text-xs font-bold bg-butter border border-black rounded-md">Late / Overdue</span>`;
        }

        return `
          <tr class="border-b border-black/15">
            <td class="py-3 px-3 font-semibold text-xs sm:text-sm">
              ${quiz.title}
              <span class="block text-[11px] text-neutral-500">Set: ${quiz.dateSet} • ${quiz.topic}</span>
            </td>
            <td class="py-3 px-3 font-display font-extrabold text-sm">
              ${res && res.score !== null ? `${res.score}/${quiz.totalMarks} (${studentPct}%)` : "—"}
            </td>
            <td class="py-3 px-3 text-xs font-bold text-neutral-600">
              ${classAvg}%
            </td>
            <td class="py-3 px-3 text-xs font-semibold">
              ${res ? res.attempts : 0}
            </td>
            <td class="py-3 px-3">
              ${statusBadge}
            </td>
            <td class="py-3 px-3 text-xs text-neutral-600">
              ${res ? res.submittedOn : "—"}
            </td>
          </tr>
        `;
      }).join("");

      // Build Study Notes Rows for this Student in this Subject
      const notesRowsHTML = subject.notes.map(note => {
        const act = student.noteActivity.find(a => a.noteId === note.id);
        const isRead = act && act.read;

        return `
          <tr class="border-b border-black/15">
            <td class="py-3 px-3 font-semibold text-xs sm:text-sm">
              ${note.title}
              <span class="block text-[11px] text-neutral-500">Topic: ${note.topic}</span>
            </td>
            <td class="py-3 px-3">
              ${
                isRead
                  ? `<span class="px-2 py-0.5 text-xs font-bold bg-mint border border-black rounded-md">Read ✓</span>`
                  : `<span class="px-2 py-0.5 text-xs font-bold bg-coral border border-black rounded-md">Unread</span>`
              }
            </td>
            <td class="py-3 px-3 text-xs font-bold">
              ${act ? act.timeSpent : "0 mins"}
            </td>
            <td class="py-3 px-3 text-xs text-neutral-600">
              ${act ? act.lastOpened : "Never"}
            </td>
          </tr>
        `;
      }).join("");

      const body = document.getElementById("studentReportBody");
      body.innerHTML = `
        <!-- Student & Subject Identity Banner -->
        <div class="brutal-card bg-white rounded-2xl p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-2xl ${student.avatarBg} border-2 border-black shadow-brutal-sm flex items-center justify-center font-display font-extrabold text-2xl shrink-0">
              ${student.initials}
            </div>
            <div>
              <div class="flex flex-wrap items-center gap-2">
                <h2 class="font-display font-extrabold text-2xl">${student.name}</h2>
                <span class="px-2.5 py-0.5 text-xs font-bold bg-cream border-2 border-black rounded-lg">${student.id}</span>
              </div>
              <p class="text-sm font-bold text-neutral-700 mt-1">
                Subject: <span class="underline">${subject.name} (${subject.code})</span> • ${subject.grade}
              </p>
              <p class="text-xs text-neutral-500 mt-0.5">Educator: <span data-report-teacher></span> • Presence: ${presence.label} • Last Active: ${presence.lastActive}</p>
            </div>
          </div>

          <div class="flex flex-wrap sm:flex-nowrap gap-3">
            <div class="bg-cream border-2 border-black rounded-xl px-4 py-2.5 text-center">
              <p class="text-[11px] font-bold uppercase text-neutral-500">Subject Avg</p>
              <p class="font-display font-extrabold text-2xl">${metrics.avgQuizPercent}%</p>
            </div>
            <div class="bg-cream border-2 border-black rounded-xl px-4 py-2.5 text-center">
              <p class="text-[11px] font-bold uppercase text-neutral-500">Notes Read</p>
              <p class="font-display font-extrabold text-2xl">${metrics.notesReadCount}/${metrics.totalNotes}</p>
            </div>
            <div class="${metrics.statusBadgeClass} border-2 border-black rounded-xl px-4 py-2.5 flex flex-col justify-center items-center">
              <p class="text-[11px] font-bold uppercase text-black/70">Standing</p>
              <p class="font-display font-extrabold text-sm mt-0.5 whitespace-nowrap">${metrics.statusLabel}</p>
            </div>
          </div>
        </div>

        <!-- Quiz Performance Breakdown -->
        <div class="space-y-2.5">
          <h4 class="font-display font-extrabold text-base sm:text-lg flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-butter border-2 border-black"></span>
            <span>Quizzes Performance Breakdown (${subject.code})</span>
          </h4>
          <div class="overflow-x-auto bg-white border-2 border-black rounded-2xl">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-cream border-b-2 border-black text-[11px] font-display font-extrabold uppercase">
                  <th class="py-2.5 px-3">Quiz Title</th>
                  <th class="py-2.5 px-3">Student Score</th>
                  <th class="py-2.5 px-3">Class Avg</th>
                  <th class="py-2.5 px-3">Attempts</th>
                  <th class="py-2.5 px-3">Status</th>
                  <th class="py-2.5 px-3">Submitted</th>
                </tr>
              </thead>
              <tbody>
                ${quizRowsHTML}
              </tbody>
            </table>
          </div>
        </div>

        <!-- Study Notes Engagement Breakdown -->
        <div class="space-y-2.5">
          <h4 class="font-display font-extrabold text-base sm:text-lg flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-sky border-2 border-black"></span>
            <span>Study Notes Engagement (${subject.code})</span>
          </h4>
          <div class="overflow-x-auto bg-white border-2 border-black rounded-2xl">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-cream border-b-2 border-black text-[11px] font-display font-extrabold uppercase">
                  <th class="py-2.5 px-3">Study Note Resource</th>
                  <th class="py-2.5 px-3">Read Status</th>
                  <th class="py-2.5 px-3">Time Spent</th>
                  <th class="py-2.5 px-3">Last Opened</th>
                </tr>
              </thead>
              <tbody>
                ${notesRowsHTML}
              </tbody>
            </table>
          </div>
        </div>

        <!-- Teacher Remarks / Feedback Box -->
        <div class="brutal-card bg-white rounded-2xl p-4 sm:p-5 space-y-3">
          <div class="flex items-center justify-between">
            <label for="teacherRemarksInput" class="font-display font-extrabold text-sm sm:text-base">
              Educator Remarks & Feedback (${subject.name})
            </label>
            <span class="text-xs font-semibold text-neutral-500 no-print">Editable before printing</span>
          </div>
          <textarea
            id="teacherRemarksInput"
            rows="3"
            class="w-full p-3 bg-cream border-2 border-black rounded-xl text-sm font-medium focus:outline-none focus:bg-white"
          ></textarea>
          <div class="flex justify-end no-print">
            <button onclick="saveStudentRemarks()" class="brutal-btn bg-mint hover:bg-emerald-300 px-4 py-2 rounded-xl font-display font-bold text-xs cursor-pointer">
              Save Remarks to Report ✓
            </button>
          </div>
        </div>
      `;

      body.querySelector("[data-report-teacher]").textContent = educatorData.teacherName;
      body.querySelector("#teacherRemarksInput").value = student.remarks;
      document.getElementById("studentReportModal").classList.remove("hidden");
    }

    function saveStudentRemarks() {
      if (!activeModalStudentId) return;
      const subject = getSelectedSubject();
      const student = subject.students.find(s => s.id === activeModalStudentId);
      const updatedText = document.getElementById("teacherRemarksInput").value.trim();
      if (student) {
        student.remarks = updatedText;
        showToast(`Saved feedback for ${student.name} (${subject.code})`);
      }
    }

    function closeStudentReport() {
      document.getElementById("studentReportModal").classList.add("hidden");
      activeModalStudentId = null;
    }

    function printStudentReport() {
      const clearPrintMode = () => delete document.body.dataset.printMode;
      document.body.dataset.printMode = "student-report";
      window.addEventListener("afterprint", clearPrintMode, { once: true });
      window.print();
    }

    // ==========================================
    // 7. QUIZ / NOTE PARTICIPATION INSPECTOR
    // ==========================================
    function openResourceInspector(type, resourceId) {
      const subject = getSelectedSubject();
      const modal = document.getElementById("resourceInspectorModal");
      const badge = document.getElementById("inspectorTypeBadge");
      const titleEl = document.getElementById("inspectorTitle");
      const bodyEl = document.getElementById("inspectorBody");

      if (type === "quiz") {
        const quiz = subject.quizzes.find(q => q.id === resourceId);
        badge.textContent = `${subject.code} • QUIZ SUBMISSIONS`;
        titleEl.textContent = quiz.title;

        bodyEl.innerHTML = subject.students.map(st => {
          const res = st.quizResults.find(r => r.quizId === resourceId);
          const hasScore = res && res.score !== null;
          const pct = hasScore ? Math.round((res.score / quiz.totalMarks) * 100) : null;

          return `
            <div class="bg-white border-2 border-black rounded-xl p-3 flex items-center justify-between gap-3">
              <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-lg ${st.avatarBg} border border-black flex items-center justify-center font-display font-bold text-xs">${st.initials}</span>
                <div>
                  <p class="font-display font-bold text-sm">${st.name}</p>
                  <p class="text-[11px] text-neutral-500">${hasScore ? `Submitted: ${res.submittedOn}` : "Not yet submitted"}</p>
                </div>
              </div>
              <div class="flex items-center gap-2">
                ${
                  hasScore
                    ? `<span class="px-2.5 py-1 text-xs font-extrabold bg-mint border border-black rounded-lg">${res.score}/${quiz.totalMarks} (${pct}%)</span>`
                    : `<span class="px-2.5 py-1 text-xs font-extrabold bg-coral border border-black rounded-lg">Missed</span>`
                }
                <button onclick="closeResourceInspector(); openStudentReport('${st.id}')" class="brutal-btn bg-butter px-2.5 py-1 rounded-lg text-xs font-bold cursor-pointer">
                  Report
                </button>
              </div>
            </div>
          `;
        }).join("");
      } else {
        const note = subject.notes.find(n => n.id === resourceId);
        badge.textContent = `${subject.code} • STUDY NOTE ENGAGEMENT`;
        titleEl.textContent = note.title;

        bodyEl.innerHTML = subject.students.map(st => {
          const act = st.noteActivity.find(a => a.noteId === resourceId);
          const isRead = act && act.read;

          return `
            <div class="bg-white border-2 border-black rounded-xl p-3 flex items-center justify-between gap-3">
              <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-lg ${st.avatarBg} border border-black flex items-center justify-center font-display font-bold text-xs">${st.initials}</span>
                <div>
                  <p class="font-display font-bold text-sm">${st.name}</p>
                  <p class="text-[11px] text-neutral-500">${isRead ? `Opened: ${act.lastOpened} • Time: ${act.timeSpent}` : "Not opened yet"}</p>
                </div>
              </div>
              <div class="flex items-center gap-2">
                ${
                  isRead
                    ? `<span class="px-2.5 py-1 text-xs font-extrabold bg-sky border border-black rounded-lg">Read ✓</span>`
                    : `<span class="px-2.5 py-1 text-xs font-extrabold bg-coral border border-black rounded-lg">Unread</span>`
                }
                <button onclick="closeResourceInspector(); openStudentReport('${st.id}')" class="brutal-btn bg-butter px-2.5 py-1 rounded-lg text-xs font-bold cursor-pointer">
                  Report
                </button>
              </div>
            </div>
          `;
        }).join("");
      }

      modal.classList.remove("hidden");
    }

    function closeResourceInspector() {
      document.getElementById("resourceInspectorModal").classList.add("hidden");
    }

    // ==========================================
    // 8. TOAST FEEDBACK
    // ==========================================
    let toastTimeout = null;
    function showToast(message) {
      const banner = document.getElementById("toastBanner");
      document.getElementById("toastMessage").textContent = message;
      banner.classList.remove("hidden");

      if (toastTimeout) clearTimeout(toastTimeout);
      toastTimeout = setTimeout(() => {
        banner.classList.add("hidden");
      }, 3200);
    }

    // Close modals on Escape key
    document.addEventListener("keydown", e => {
      if (e.key === "Escape") {
        closeStudentReport();
        closeResourceInspector();
      }
    });

    // ==========================================
    // 9. INITIALIZE DASHBOARD ON LOAD
    // ==========================================
    window.addEventListener("DOMContentLoaded", () => {
      selectSubject(selectedSubjectId);
    });

    Object.assign(window, { showToast, switchWorkspaceTab, setPerformanceFilter, openStudentReport, openResourceInspector, closeResourceInspector, closeStudentReport, printStudentReport, saveStudentRemarks });
}

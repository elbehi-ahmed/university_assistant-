"use strict";
/*
 * UniAssistant — app.js
 * One file for every page.
 * Add  <script src="app.js"></script>  just before </body> on every HTML page.
 */

/* =========================================================
   1.  STORE  –  all data lives in localStorage
   ========================================================= */
const Store = (() => {
  const KEY = "uniassistant_v4";

  const SEED = {
    currentUser: null,
    users: [
      {
        id: 1,
        name: "Ahmed Ben Ali",
        email: "ahmed.benali@insat.ucar.tn",
        password: "Password1!",
        role: "student",
        avatar: "A",
        studentId: "2024INSAT123",
        notifications: { exams: true, tasks: true, summary: false },
      },
      {
        id: 2,
        name: "Dr. Sarah Mansouri",
        email: "sarah.mansouri@insat.ucar.tn",
        password: "Teacher1!",
        role: "teacher",
        avatar: "S",
        department: "Computer Science",
        notifications: { submissions: true, exams: true, summary: false },
        teachingPrefs: { gradingScale: "Points (0-20)", autoAverage: true },
      },
    ],
    subjects: [
      {
        id: 1,
        name: "Mathematics",
        coefficient: 4,
        icon: "fa-square-root-alt",
        hue: 38,
        userId: 1,
      },
      {
        id: 2,
        name: "Physics",
        coefficient: 3,
        icon: "fa-atom",
        hue: 200,
        userId: 1,
      },
      {
        id: 3,
        name: "English",
        coefficient: 2,
        icon: "fa-language",
        hue: 140,
        userId: 1,
      },
      {
        id: 4,
        name: "Algorithms",
        coefficient: 4,
        icon: "fa-code",
        hue: 280,
        userId: 1,
      },
      {
        id: 5,
        name: "Databases",
        coefficient: 3,
        icon: "fa-database",
        hue: 170,
        userId: 1,
      },
      {
        id: 6,
        name: "Networks",
        coefficient: 3,
        icon: "fa-network-wired",
        hue: 10,
        userId: 1,
      },
    ],
    tasks: [
      {
        id: 1,
        title: "Math Homework — Integration",
        description: "Exercises 4.1–4.8 on definite integrals",
        subjectId: 1,
        deadline: "2025-04-02",
        priority: "high",
        status: "todo",
        progress: 30,
        userId: 1,
      },
      {
        id: 2,
        title: "Physics Lab Report",
        description: "Write-up for electromagnetic induction experiment",
        subjectId: 2,
        deadline: "2025-04-05",
        priority: "medium",
        status: "todo",
        progress: 60,
        userId: 1,
      },
      {
        id: 3,
        title: "English Presentation Slides",
        description: "10-slide deck on climate change solutions",
        subjectId: 3,
        deadline: "2025-04-10",
        priority: "low",
        status: "todo",
        progress: 80,
        userId: 1,
      },
      {
        id: 4,
        title: "Algorithm Complexity Analysis",
        description: "Big-O notation exercises chapter 3",
        subjectId: 4,
        deadline: "2025-03-20",
        priority: "high",
        status: "done",
        progress: 100,
        userId: 1,
      },
      {
        id: 5,
        title: "Database ER Diagram",
        description: "Design ER schema for library system",
        subjectId: 5,
        deadline: "2025-03-18",
        priority: "medium",
        status: "done",
        progress: 100,
        userId: 1,
      },
      {
        id: 6,
        title: "Networks OSI Model Summary",
        description: "1-page summary of the 7 layers",
        subjectId: 6,
        deadline: "2025-03-15",
        priority: "low",
        status: "done",
        progress: 100,
        userId: 1,
      },
    ],
    exams: [
      {
        id: 1,
        subjectId: 1,
        date: "2025-04-03",
        time: "09:00",
        room: "Room A12",
        duration: "2 hours",
        notes: "",
        userId: 1,
      },
      {
        id: 2,
        subjectId: 2,
        date: "2025-04-10",
        time: "14:00",
        room: "Room B04",
        duration: "2 hours",
        notes: "",
        userId: 1,
      },
      {
        id: 3,
        subjectId: 4,
        date: "2025-03-15",
        time: "09:00",
        room: "Room C07",
        duration: "2 hours",
        notes: "",
        userId: 1,
      },
      {
        id: 4,
        subjectId: 3,
        date: "2025-03-08",
        time: "10:00",
        room: "Room A02",
        duration: "2 hours",
        notes: "",
        userId: 1,
      },
    ],
    nextId: { subjects: 7, tasks: 7, exams: 5 },
  };

  function _load() {
    try {
      const r = localStorage.getItem(KEY);
      return r ? JSON.parse(r) : _clone();
    } catch (e) {
      return _clone();
    }
  }
  function _clone() {
    return JSON.parse(JSON.stringify(SEED));
  }
  function _save(d) {
    localStorage.setItem(KEY, JSON.stringify(d));
  }

  let D = _load();

  return {
    get() {
      return D;
    },
    save() {
      _save(D);
    },
    reset() {
      D = _clone();
      _save(D);
    },

    // Auth
    login(email, pw) {
      const u = D.users.find((u) => u.email === email && u.password === pw);
      if (!u) return null;
      D.currentUser = u;
      _save(D);
      return u;
    },
    logout() {
      D.currentUser = null;
      _save(D);
    },
    me() {
      return D.currentUser;
    },

    // Subjects
    getSubjects(uid) {
      return D.subjects.filter((s) => s.userId === uid);
    },
    addSubject(s) {
      s.id = D.nextId.subjects++;
      D.subjects.push(s);
      _save(D);
      return s;
    },
    updateSubject(id, p) {
      const s = D.subjects.find((s) => s.id === id);
      if (s) {
        Object.assign(s, p);
        _save(D);
      }
    },
    deleteSubject(id) {
      D.subjects = D.subjects.filter((s) => s.id !== id);
      D.tasks = D.tasks.filter((t) => t.subjectId !== id);
      D.exams = D.exams.filter((e) => e.subjectId !== id);
      _save(D);
    },

    // Tasks
    getTasks(uid) {
      return D.tasks.filter((t) => t.userId === uid);
    },
    addTask(t) {
      t.id = D.nextId.tasks++;
      D.tasks.push(t);
      _save(D);
      return t;
    },
    updateTask(id, p) {
      const t = D.tasks.find((t) => t.id === id);
      if (t) {
        Object.assign(t, p);
        _save(D);
      }
    },
    deleteTask(id) {
      D.tasks = D.tasks.filter((t) => t.id !== id);
      _save(D);
    },

    // Exams
    getExams(uid) {
      return D.exams.filter((e) => e.userId === uid);
    },
    addExam(e) {
      e.id = D.nextId.exams++;
      D.exams.push(e);
      _save(D);
      return e;
    },
    updateExam(id, p) {
      const e = D.exams.find((e) => e.id === id);
      if (e) {
        Object.assign(e, p);
        _save(D);
      }
    },
    deleteExam(id) {
      D.exams = D.exams.filter((e) => e.id !== id);
      _save(D);
    },

    // Settings
    saveSettings(uid, p) {
      const u = D.users.find((u) => u.id === uid);
      if (u) {
        Object.assign(u, p);
        D.currentUser = u;
        _save(D);
      }
    },
  };
})();

/* =========================================================
   2.  HELPERS
   ========================================================= */

// Subject helpers
function subjectById(id) {
  return Store.get().subjects.find((s) => s.id === +id) || null;
}
function subjectName(id) {
  return subjectById(id)?.name || "—";
}

// Date helpers
function daysUntil(ds) {
  const now = new Date();
  now.setHours(0, 0, 0, 0);
  return Math.round((new Date(ds + "T00:00:00") - now) / 86400000);
}
function fmtDate(ds) {
  if (!ds) return "—";
  return new Date(ds + "T00:00:00").toLocaleDateString("en-GB", {
    day: "2-digit",
    month: "short",
    year: "numeric",
  });
}
function fmtMonth(ds) {
  return new Date(ds + "T00:00:00").toLocaleString("en-GB", { month: "long" });
}
function fmtDay(ds) {
  return String(new Date(ds + "T00:00:00").getDate()).padStart(2, "0");
}
function fmtYear(ds) {
  return new Date(ds + "T00:00:00").getFullYear();
}

// Auto priority from deadline
function autoPriority(ds) {
  const d = daysUntil(ds);
  return d <= 2 ? "high" : d <= 7 ? "medium" : "low";
}

// Set input value safely
function setVal(id, val) {
  const el = document.getElementById(id);
  if (el) el.value = val;
}
function setChecked(id, val) {
  const el = document.getElementById(id);
  if (el) el.checked = val;
}
function getChecked(id) {
  return !!document.getElementById(id)?.checked;
}

/* ----  Toast  ---- */
function toast(msg, type) {
  type = type || "success";
  let box = document.getElementById("_toasts");
  if (!box) {
    box = document.createElement("div");
    box.id = "_toasts";
    box.style.cssText =
      "position:fixed;bottom:24px;right:24px;z-index:9999;display:flex;flex-direction:column;gap:10px;pointer-events:none";
    document.body.appendChild(box);
  }
  const C = {
    success: "#4caf82",
    danger: "#e05c5c",
    info: "#4c8fe0",
    warning: "#e0a84c",
  };
  const I = {
    success: "fa-check-circle",
    danger: "fa-times-circle",
    info: "fa-info-circle",
    warning: "fa-exclamation-circle",
  };
  const c = C[type] || C.success;
  const i = I[type] || I.success;
  const el = document.createElement("div");
  el.style.cssText =
    "background:linear-gradient(135deg,#1e304a,#162236);border:1px solid " +
    c +
    ";color:#f5f0e8;padding:13px 20px;border-radius:12px;font-size:.875rem;box-shadow:0 8px 32px rgba(0,0,0,.5);display:flex;align-items:center;gap:10px;min-width:240px;pointer-events:all";
  el.innerHTML =
    '<i class="fas ' +
    i +
    '" style="color:' +
    c +
    ';font-size:1rem"></i><span>' +
    msg +
    "</span>";
  box.appendChild(el);
  setTimeout(function () {
    el.style.transition = "opacity .4s";
    el.style.opacity = "0";
    setTimeout(function () {
      el.remove();
    }, 420);
  }, 3000);
}

/* ----  Confirm dialog  ---- */
function confirmDialog(msg) {
  return new Promise(function (resolve) {
    const ov = document.createElement("div");
    ov.style.cssText =
      "position:fixed;inset:0;background:rgba(0,0,0,.65);backdrop-filter:blur(4px);z-index:10000;display:flex;align-items:center;justify-content:center;padding:20px";
    ov.innerHTML =
      '<div style="background:linear-gradient(145deg,#1a2e46,#121f32);border:1px solid rgba(201,168,76,.25);border-radius:18px;padding:36px 32px;max-width:380px;width:100%;text-align:center;box-shadow:0 30px 80px rgba(0,0,0,.6)">' +
      '<i class="fas fa-exclamation-triangle" style="font-size:2.2rem;color:#e05c5c;margin-bottom:16px;display:block"></i>' +
      '<p style="font-size:.9rem;color:rgba(245,240,232,.85);line-height:1.7;margin-bottom:26px">' +
      msg +
      "</p>" +
      '<div style="display:flex;gap:12px;justify-content:center">' +
      '<button id="_cno"  style="padding:10px 26px;border-radius:8px;background:rgba(255,255,255,.06);border:1px solid rgba(201,168,76,.2);color:rgba(245,240,232,.75);cursor:pointer;font-size:.875rem;font-family:inherit">Cancel</button>' +
      '<button id="_cyes" style="padding:10px 26px;border-radius:8px;background:linear-gradient(135deg,#e05c5c,#b02020);border:none;color:#fff;cursor:pointer;font-size:.875rem;font-weight:600;font-family:inherit">Delete</button>' +
      "</div>" +
      "</div>";
    document.body.appendChild(ov);
    ov.querySelector("#_cyes").onclick = function () {
      ov.remove();
      resolve(true);
    };
    ov.querySelector("#_cno").onclick = function () {
      ov.remove();
      resolve(false);
    };
  });
}

/* ----  Auth guard  ---- */
function requireAuth(role) {
  const u = Store.me();
  if (!u) {
    location.href = "login.html";
    return null;
  }
  if (role && u.role !== role) {
    location.href =
      u.role === "teacher" ? "teacher-dashboard.html" : "dashboard.html";
    return null;
  }
  return u;
}

/* ----  Fill sidebar  ---- */
function fillSidebar(user) {
  document.querySelectorAll(".user-name").forEach(function (el) {
    el.textContent = user.name;
  });
  document.querySelectorAll(".user-avatar").forEach(function (el) {
    el.textContent = user.avatar || user.name[0];
  });
  document.querySelectorAll(".user-role").forEach(function (el) {
    el.textContent = user.role === "teacher" ? "ROLE_TEACHER" : "ROLE_ETUDIANT";
  });
}

/* ----  Update nav badges  ---- */
function updateBadges(user) {
  const pending = Store.getTasks(user.id).filter(function (t) {
    return t.status === "todo";
  }).length;
  const upcoming = Store.getExams(user.id).filter(function (e) {
    return daysUntil(e.date) >= 0;
  }).length;
  document
    .querySelectorAll('a[href="taches.html"] .nav-badge')
    .forEach(function (el) {
      el.textContent = pending;
    });
  document
    .querySelectorAll('a[href="examens.html"] .nav-badge')
    .forEach(function (el) {
      el.textContent = upcoming;
    });
}

/* ----  Shared: sidebar toggle  ---- */
function toggleSidebar() {
  const sb = document.getElementById("sidebar");
  if (sb) sb.classList.toggle("open");
}

/* ----  Shared: open/close any modal by id  ---- */
function openModalById(id) {
  const el = document.getElementById(id);
  if (el) el.classList.add("open");
}
function closeModalById(id) {
  const el = document.getElementById(id);
  if (el) el.classList.remove("open");
}

/* =========================================================
   3.  LOGIN PAGE  –  login.html
   ========================================================= */
function initLogin() {
  /* password show/hide */
  window.togglePw = function () {
    const inp = document.getElementById("password");
    const icon = document.getElementById("pw-icon");
    if (!inp) return;
    if (inp.type === "password") {
      inp.type = "text";
      if (icon) {
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
      }
    } else {
      inp.type = "password";
      if (icon) {
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
      }
    }
  };

  /* demo credentials hint */
  const card = document.querySelector(".login-card");
  if (card) {
    const hint = document.createElement("p");
    hint.style.cssText =
      "text-align:center;font-size:.72rem;color:rgba(245,240,232,.38);margin-top:18px;line-height:1.9";
    hint.innerHTML =
      'Student &nbsp;→&nbsp; <b style="color:#e2c47a">ahmed.benali@insat.ucar.tn</b> / <b style="color:#e2c47a">Password1!</b><br>' +
      'Teacher &nbsp;→&nbsp; <b style="color:#82b8f0">sarah.mansouri@insat.ucar.tn</b> / <b style="color:#82b8f0">Teacher1!</b>';
    card.appendChild(hint);
  }

  /* form submit */
  const form = document.querySelector("form");
  if (!form) return;
  form.addEventListener("submit", function (e) {
    e.preventDefault();
    const email = (document.getElementById("email")?.value || "").trim();
    const pw = document.getElementById("password")?.value || "";
    const user = Store.login(email, pw);
    if (!user) {
      toast("Invalid email or password", "danger");
      return;
    }
    toast("Welcome back, " + user.name.split(" ")[0] + "!", "success");
    setTimeout(function () {
      location.href =
        user.role === "teacher" ? "teacher-dashboard.html" : "dashboard.html";
    }, 700);
  });
}

/* =========================================================
   4.  REGISTER PAGE  –  register.html
   ========================================================= */
function initRegister() {
  /* password show/hide (two fields) */
  window.togglePw = function (inputId, iconId) {
    const inp = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    if (!inp) return;
    inp.type = inp.type === "password" ? "text" : "password";
    if (icon) {
      icon.classList.toggle("fa-eye");
      icon.classList.toggle("fa-eye-slash");
    }
  };

  /* password strength meter */
  const pwField = document.getElementById("password");
  if (pwField) {
    pwField.addEventListener("input", function () {
      const v = this.value;
      const bar = document.getElementById("pw-bar");
      const lbl = document.getElementById("pw-label");
      if (!bar || !lbl) return;
      let score = 0;
      if (v.length >= 8) score++;
      if (/[A-Z]/.test(v)) score++;
      if (/[0-9]/.test(v)) score++;
      if (/[^A-Za-z0-9]/.test(v)) score++;
      const labels = ["", "Weak", "Fair", "Good", "Strong"];
      const colors = ["", "#e05c5c", "#e0a84c", "#4c8fe0", "#4caf82"];
      bar.style.width = score * 25 + "%";
      bar.style.background = colors[score] || "transparent";
      lbl.textContent = labels[score] || "";
      lbl.style.color = colors[score] || "transparent";
    });
  }

  /* form submit */
  const form = document.getElementById("register-form");
  if (!form) return;
  form.addEventListener("submit", function (e) {
    e.preventDefault();
    const first = (document.getElementById("firstname")?.value || "").trim();
    const last = (document.getElementById("lastname")?.value || "").trim();
    const email = (document.getElementById("email")?.value || "").trim();
    const pw = document.getElementById("password")?.value || "";
    const conf = document.getElementById("confirm")?.value || "";

    if (!first || !last || !email) {
      toast("Please fill all fields", "warning");
      return;
    }
    if (pw.length < 8) {
      toast("Password must be at least 8 characters", "warning");
      return;
    }
    if (pw !== conf) {
      toast("Passwords do not match", "danger");
      return;
    }

    const data = Store.get();
    if (
      data.users.find(function (u) {
        return u.email === email;
      })
    ) {
      toast("Email already registered", "danger");
      return;
    }

    data.users.push({
      id: Date.now(),
      name: first + " " + last,
      email: email,
      password: pw,
      role: "student",
      avatar: first[0].toUpperCase(),
      notifications: { exams: true, tasks: true, summary: false },
    });
    Store.save();
    toast("Account created! Redirecting…", "success");
    setTimeout(function () {
      location.href = "login.html";
    }, 1200);
  });
}

/* =========================================================
   5.  STUDENT DASHBOARD  –  dashboard.html
   ========================================================= */
function initDashboard() {
  const user = requireAuth("student");
  if (!user) return;
  fillSidebar(user);
  updateBadges(user);

  const tasks = Store.getTasks(user.id);
  const exams = Store.getExams(user.id);
  const subjects = Store.getSubjects(user.id);
  const todo = tasks.filter(function (t) {
    return t.status === "todo";
  });
  const done = tasks.filter(function (t) {
    return t.status === "done";
  });
  const upcoming = exams
    .filter(function (e) {
      return daysUntil(e.date) >= 0;
    })
    .sort(function (a, b) {
      return new Date(a.date) - new Date(b.date);
    });

  /* ── stat cards ── */
  const cards = document.querySelectorAll(".stat-card");
  if (cards[0])
    cards[0].querySelector(".stat-value").textContent = subjects.length;
  if (cards[1]) cards[1].querySelector(".stat-value").textContent = todo.length;
  if (cards[2]) cards[2].querySelector(".stat-value").textContent = done.length;
  if (cards[3]) {
    cards[3].querySelector(".stat-value").textContent = upcoming.length;
    if (upcoming[0]) {
      const d = daysUntil(upcoming[0].date);
      const ch = cards[3].querySelector(".stat-change");
      if (ch)
        ch.innerHTML =
          '<i class="fas fa-clock"></i> Next: ' +
          d +
          " day" +
          (d !== 1 ? "s" : "");
    }
  }

  /* breadcrumb */
  const bc = document.querySelector(".page-breadcrumb");
  if (bc)
    bc.innerHTML = "Welcome back, <span>" + user.name.split(" ")[0] + "</span>";

  /* ── urgent tasks ── */
  const taskList = document.querySelector(".task-list");
  if (taskList) {
    taskList.innerHTML = "";
    const urgent = todo
      .slice()
      .sort(function (a, b) {
        return new Date(a.deadline) - new Date(b.deadline);
      })
      .slice(0, 3);

    if (!urgent.length) {
      taskList.innerHTML =
        '<div style="text-align:center;padding:32px 0;color:var(--muted)">' +
        '<i class="fas fa-check-circle" style="font-size:2rem;display:block;margin-bottom:10px;color:var(--success)"></i>' +
        "All caught up!" +
        "</div>";
    } else {
      urgent.forEach(function (task) {
        const subj = subjectById(task.subjectId);
        const days = daysUntil(task.deadline);
        const due =
          days < 0
            ? "Overdue!"
            : days === 0
              ? "Due today"
              : days === 1
                ? "Due tomorrow"
                : "Due in " + days + " days";
        const pc =
          task.priority === "high"
            ? "danger"
            : task.priority === "medium"
              ? "warning"
              : "info";
        const bc2 =
          task.priority === "high"
            ? "badge-danger"
            : task.priority === "medium"
              ? "badge-warning"
              : "badge-info";
        const bl =
          task.priority === "high"
            ? "High priority"
            : task.priority === "medium"
              ? "Medium"
              : "Low";
        const pct = task.progress || 0;
        const barcls = task.priority === "high" ? "danger" : "";
        taskList.insertAdjacentHTML(
          "beforeend",
          '<div class="task-item' +
            (task.priority === "high" ? " urgent" : "") +
            '">' +
            '<div class="task-priority-bar ' +
            pc +
            '"></div>' +
            '<div class="task-content">' +
            '<div class="task-title">' +
            task.title +
            "</div>" +
            '<div class="task-meta">' +
            '<span class="badge ' +
            bc2 +
            '">' +
            bl +
            "</span>" +
            '<span class="task-subject">' +
            (subj ? subj.name : "—") +
            "</span>" +
            '<span class="task-date"><i class="far fa-clock"></i> ' +
            due +
            "</span>" +
            "</div>" +
            '<div class="progress-wrap mt-8">' +
            '<div class="progress-bar ' +
            barcls +
            '" style="width:' +
            pct +
            '%"></div>' +
            "</div>" +
            "</div>" +
            '<div class="task-actions">' +
            '<button class="btn btn-ghost btn-icon btn-sm" onclick="location.href=\'taches.html\'" data-tip="Go to tasks"><i class="fas fa-pen"></i></button>' +
            "</div>" +
            "</div>",
        );
      });
    }
  }

  /* ── upcoming exams ── */
  const examList = document.querySelector(".exam-list");
  if (examList) {
    examList.innerHTML = "";
    if (!upcoming.length) {
      examList.innerHTML =
        '<p style="color:var(--muted);font-size:.85rem">No upcoming exams.</p>';
    } else {
      upcoming.slice(0, 2).forEach(function (exam) {
        const subj = subjectById(exam.subjectId);
        const days = daysUntil(exam.date);
        const bc3 = days <= 7 ? "badge-danger" : "badge-warning";
        examList.insertAdjacentHTML(
          "beforeend",
          '<div class="exam-item">' +
            '<div class="exam-date-box">' +
            '<span class="exam-day">' +
            fmtDay(exam.date) +
            "</span>" +
            '<span class="exam-month">' +
            fmtMonth(exam.date).slice(0, 3) +
            "</span>" +
            "</div>" +
            '<div class="exam-info">' +
            '<div class="exam-subject">' +
            (subj ? subj.name : "—") +
            "</div>" +
            '<div class="exam-detail"><i class="fas fa-map-marker-alt"></i> ' +
            exam.room +
            ' &nbsp;·&nbsp; <i class="far fa-clock"></i> ' +
            exam.time +
            "</div>" +
            "</div>" +
            '<span class="badge ' +
            bc3 +
            '">' +
            days +
            " day" +
            (days !== 1 ? "s" : "") +
            "</span>" +
            "</div>",
        );
      });
    }
  }

  /* ── semester progress ── */
  const progList = document.querySelector(".subject-progress-list");
  if (progList) {
    progList.innerHTML = "";
    subjects.forEach(function (s) {
      const st = tasks.filter(function (t) {
        return t.subjectId === s.id;
      });
      const pct = st.length
        ? Math.round(
            (st.filter(function (t) {
              return t.status === "done";
            }).length /
              st.length) *
              100,
          )
        : 0;
      const cls = pct >= 80 ? "success" : pct < 40 ? "danger" : "";
      progList.insertAdjacentHTML(
        "beforeend",
        '<div class="sp-item">' +
          '<div class="flex justify-between mb-8">' +
          '<span class="sp-name">' +
          s.name +
          "</span>" +
          '<span class="sp-pct">' +
          pct +
          "%</span>" +
          "</div>" +
          '<div class="progress-wrap"><div class="progress-bar ' +
          cls +
          '" style="width:' +
          pct +
          '%"></div></div>' +
          "</div>",
      );
    });
  }
}

/* =========================================================
   6.  SUBJECTS PAGE  –  matieres.html
   ========================================================= */
function initSubjects() {
  const user = requireAuth("student");
  if (!user) return;
  fillSidebar(user);
  updateBadges(user);

  const ICON_POOL = [
    { icon: "fa-square-root-alt", hue: 38 },
    { icon: "fa-atom", hue: 200 },
    { icon: "fa-language", hue: 140 },
    { icon: "fa-code", hue: 280 },
    { icon: "fa-database", hue: 170 },
    { icon: "fa-network-wired", hue: 10 },
    { icon: "fa-flask", hue: 60 },
    { icon: "fa-book", hue: 320 },
  ];

  let editingId = null;

  /* ── render grid ── */
  function renderGrid() {
    const grid = document.getElementById("subjects-grid");
    if (!grid) return;
    const subs = Store.getSubjects(user.id);
    const tasks = Store.getTasks(user.id);
    const exams = Store.getExams(user.id);

    if (!subs.length) {
      grid.innerHTML =
        '<div class="empty-state">' +
        '<div class="empty-icon"><i class="fas fa-book-open"></i></div>' +
        '<div class="empty-title">No subjects yet</div>' +
        '<div class="empty-desc">Click "Add subject" to get started.</div>' +
        "</div>";
      return;
    }

    grid.innerHTML = subs
      .map(function (s) {
        const tc = tasks.filter(function (t) {
          return t.subjectId === s.id && t.status === "todo";
        }).length;
        const ec = exams.filter(function (e) {
          return e.subjectId === s.id && daysUntil(e.date) >= 0;
        }).length;
        const tb = tc
          ? '<span class="badge badge-warning">' +
            tc +
            " task" +
            (tc !== 1 ? "s" : "") +
            "</span>"
          : '<span class="badge badge-muted">0 tasks</span>';
        const eb = ec
          ? '<span class="badge badge-danger">' +
            ec +
            " exam" +
            (ec !== 1 ? "s" : "") +
            "</span>"
          : '<span class="badge badge-muted">0 exams</span>';
        return (
          '<div class="subject-card" data-name="' +
          s.name.toLowerCase() +
          '">' +
          '<div class="subject-icon" style="--hue:' +
          (s.hue || 38) +
          '">' +
          '<i class="fas ' +
          (s.icon || "fa-book") +
          '"></i>' +
          "</div>" +
          '<div class="subject-info">' +
          '<div class="subject-name">' +
          s.name +
          "</div>" +
          '<div class="subject-coeff">Coefficient <strong>' +
          s.coefficient +
          "</strong></div>" +
          "</div>" +
          '<div class="subject-stats">' +
          tb +
          eb +
          "</div>" +
          '<div class="subject-actions">' +
          '<button class="btn btn-ghost btn-icon btn-sm" data-tip="Edit"   onclick="openModal(\'edit\',' +
          s.id +
          ')"><i class="fas fa-pen"></i></button>' +
          '<button class="btn btn-danger btn-icon btn-sm" data-tip="Delete" onclick="confirmDelete(\'' +
          s.name.replace(/'/g, "\\'") +
          "'," +
          s.id +
          ')"><i class="fas fa-trash"></i></button>' +
          "</div>" +
          "</div>"
        );
      })
      .join("");
  }

  /* ── modal open ── */
  window.openModal = function (mode, id) {
    mode = mode || "add";
    editingId = null;
    const titleEl = document.getElementById("modal-title");
    const nameEl = document.getElementById("m-name");
    const coeffEl = document.getElementById("m-coeff");

    if (mode === "edit" && id) {
      editingId = +id;
      const s = Store.get().subjects.find(function (s) {
        return s.id === editingId;
      });
      if (!s) return;
      if (titleEl) titleEl.textContent = "Edit subject";
      if (nameEl) nameEl.value = s.name;
      if (coeffEl) coeffEl.value = s.coefficient;
    } else {
      if (titleEl) titleEl.textContent = "Add subject";
      if (nameEl) nameEl.value = "";
      if (coeffEl) coeffEl.value = "";
    }
    openModalById("subject-modal");
  };

  /* ── modal close ── */
  window.closeModal = function () {
    closeModalById("subject-modal");
  };

  /* ── overlay click ── */
  window.handleOverlayClick = function (e) {
    if (e.target === e.currentTarget) window.closeModal();
  };

  /* ── save subject ── */
  window.saveSubject = function () {
    const name = (document.getElementById("m-name")?.value || "").trim();
    const coeff = parseInt(document.getElementById("m-coeff")?.value || "");
    if (!name) {
      toast("Please enter a subject name", "warning");
      return;
    }
    if (isNaN(coeff) || coeff < 1 || coeff > 10) {
      toast("Coefficient must be between 1 and 10", "warning");
      return;
    }

    if (editingId) {
      Store.updateSubject(editingId, { name: name, coefficient: coeff });
      toast("Subject updated", "success");
    } else {
      const pick =
        ICON_POOL[Store.getSubjects(user.id).length % ICON_POOL.length];
      Store.addSubject({
        name: name,
        coefficient: coeff,
        icon: pick.icon,
        hue: pick.hue,
        userId: user.id,
      });
      toast("Subject added", "success");
    }
    window.closeModal();
    renderGrid();
    updateBadges(user);
  };

  /* ── confirm delete ── */
  window.confirmDelete = function (name, id) {
    const nameEl = document.getElementById("delete-name");
    if (nameEl) nameEl.textContent = name;

    // attach the actual delete action to the delete button
    const delBtn = document.querySelector("#delete-modal .btn-danger");
    if (delBtn) {
      delBtn.onclick = function () {
        Store.deleteSubject(+id);
        toast("Subject deleted", "danger");
        window.closeDeleteModal();
        renderGrid();
        updateBadges(user);
      };
    }
    openModalById("delete-modal");
  };

  window.closeDeleteModal = function () {
    closeModalById("delete-modal");
  };

  /* ── search filter ── */
  window.filterSubjects = function (q) {
    document.querySelectorAll(".subject-card[data-name]").forEach(function (c) {
      c.style.display = c.dataset.name.includes(q.toLowerCase()) ? "" : "none";
    });
  };

  renderGrid();
}

/* =========================================================
   7.  TASKS PAGE  –  taches.html
   ========================================================= */
function initTasks() {
  const user = requireAuth("student");
  if (!user) return;
  fillSidebar(user);
  updateBadges(user);

  let editingId = null;
  let fStatus = "";
  let fPriority = "";
  let fQuery = "";

  /* ── fill subject dropdown in modal ── */
  function fillSubjectSelect() {
    const sel = document.getElementById("t-subject");
    if (!sel) return;
    sel.innerHTML =
      '<option value="">Select subject</option>' +
      Store.getSubjects(user.id)
        .map(function (s) {
          return '<option value="' + s.id + '">' + s.name + "</option>";
        })
        .join("");
  }

  /* ── render table ── */
  function renderTable() {
    const tbody = document.getElementById("tasks-body");
    if (!tbody) return;

    let list = Store.getTasks(user.id);
    if (fStatus)
      list = list.filter(function (t) {
        return t.status === fStatus;
      });
    if (fPriority)
      list = list.filter(function (t) {
        return t.priority === fPriority;
      });
    if (fQuery)
      list = list.filter(function (t) {
        return t.title.toLowerCase().includes(fQuery);
      });

    if (!list.length) {
      tbody.innerHTML =
        '<tr><td colspan="7" style="text-align:center;padding:44px;color:var(--muted)">' +
        '<i class="fas fa-clipboard-list" style="font-size:2.2rem;display:block;margin-bottom:12px;opacity:.3"></i>' +
        "No tasks found" +
        "</td></tr>";
      refreshPills();
      return;
    }

    tbody.innerHTML = list
      .map(function (t) {
        const subj = subjectById(t.subjectId);
        const days = daysUntil(t.deadline);
        const dc = days < 0 ? "urgent" : days <= 3 ? "soon" : "";
        const dlbl =
          days < 0 ? "Overdue (" + Math.abs(days) + "d)" : fmtDate(t.deadline);
        const pc =
          t.priority === "high"
            ? "badge-danger"
            : t.priority === "medium"
              ? "badge-warning"
              : "badge-success";
        const pl = t.priority.charAt(0).toUpperCase() + t.priority.slice(1);
        const sc = t.status === "done" ? "done" : "todo";
        const sl = t.status === "done" ? "Done" : "To do";
        const ntc = t.status === "done" ? "done-text" : "";
        const chk = t.status === "done" ? "checked" : "";
        const sb = subj
          ? '<span class="badge badge-gold">' + subj.name + "</span>"
          : '<span class="badge badge-muted">—</span>';

        return (
          '<tr data-id="' +
          t.id +
          '" data-status="' +
          t.status +
          '" data-priority="' +
          t.priority +
          '">' +
          '<td><input type="checkbox" class="task-check" ' +
          chk +
          ' onchange="toggleDone(' +
          t.id +
          ',this.checked)"/></td>' +
          "<td>" +
          '<div class="task-name ' +
          ntc +
          '">' +
          t.title +
          "</div>" +
          '<div class="task-desc-preview">' +
          (t.description || "") +
          "</div>" +
          "</td>" +
          "<td>" +
          sb +
          "</td>" +
          '<td><span class="deadline ' +
          dc +
          '"><i class="far fa-clock"></i> ' +
          dlbl +
          "</span></td>" +
          '<td><span class="badge ' +
          pc +
          '">' +
          pl +
          "</span></td>" +
          '<td><span class="status-pill ' +
          sc +
          '">' +
          sl +
          "</span></td>" +
          "<td>" +
          '<div class="row-actions">' +
          '<button class="btn btn-ghost btn-icon btn-sm" data-tip="Edit"   onclick="openModal(' +
          t.id +
          ')"><i class="fas fa-pen"></i></button>' +
          '<button class="btn btn-danger btn-icon btn-sm" data-tip="Delete" onclick="deleteTask(' +
          t.id +
          ')"><i class="fas fa-trash"></i></button>' +
          "</div>" +
          "</td>" +
          "</tr>"
        );
      })
      .join("");

    refreshPills();
  }

  /* ── pill counters ── */
  function refreshPills() {
    const all = Store.getTasks(user.id);
    const todo = all.filter(function (t) {
      return t.status === "todo";
    }).length;
    const done = all.filter(function (t) {
      return t.status === "done";
    }).length;
    const pills = document.querySelectorAll(".summary-pill .pill-count");
    if (pills[0]) pills[0].textContent = all.length;
    if (pills[1]) pills[1].textContent = todo;
    if (pills[2]) pills[2].textContent = done;
  }

  /* ── open modal (add or edit) ── */
  window.openModal = function (id) {
    editingId = id ? +id : null;
    fillSubjectSelect();

    const titleEl = document.getElementById("task-modal-title");
    if (editingId) {
      const t = Store.getTasks(user.id).find(function (t) {
        return t.id === editingId;
      });
      if (!t) return;
      if (titleEl) titleEl.textContent = "Edit task";
      setVal("t-title", t.title);
      setVal("t-desc", t.description || "");
      setVal("t-subject", t.subjectId || "");
      setVal("t-deadline", t.deadline || "");
      setVal("t-priority", t.priority);
      setVal("t-status", t.status);
    } else {
      if (titleEl) titleEl.textContent = "New task";
      setVal("t-title", "");
      setVal("t-desc", "");
      setVal("t-subject", "");
      setVal("t-deadline", "");
      setVal("t-priority", "medium");
      setVal("t-status", "todo");
    }
    openModalById("task-modal");
  };

  window.closeModal = function () {
    closeModalById("task-modal");
  };

  window.handleOverlayClick = function (e) {
    if (e.target === e.currentTarget) window.closeModal();
  };

  /* ── save task (modal Save button) ── */
  const saveBtn = document.querySelector(
    "#task-modal .modal-footer .btn-primary",
  );
  if (saveBtn) {
    saveBtn.onclick = function () {
      const title = (document.getElementById("t-title")?.value || "").trim();
      const desc = (document.getElementById("t-desc")?.value || "").trim();
      const subjId =
        parseInt(document.getElementById("t-subject")?.value) || null;
      const deadline = document.getElementById("t-deadline")?.value || "";
      const priority =
        document.getElementById("t-priority")?.value || autoPriority(deadline);
      const status = document.getElementById("t-status")?.value || "todo";

      if (!title) {
        toast("Please enter a title", "warning");
        return;
      }
      if (!deadline) {
        toast("Please set a deadline", "warning");
        return;
      }

      const progress = status === "done" ? 100 : 0;

      if (editingId) {
        Store.updateTask(editingId, {
          title: title,
          description: desc,
          subjectId: subjId,
          deadline: deadline,
          priority: priority,
          status: status,
          progress: progress,
        });
        toast("Task updated", "success");
      } else {
        Store.addTask({
          title: title,
          description: desc,
          subjectId: subjId,
          deadline: deadline,
          priority: priority,
          status: status,
          progress: progress,
          userId: user.id,
        });
        toast("Task added", "success");
      }
      window.closeModal();
      renderTable();
      updateBadges(user);
    };
  }

  /* ── checkbox toggle ── */
  window.toggleDone = function (id, checked) {
    Store.updateTask(+id, {
      status: checked ? "done" : "todo",
      progress: checked ? 100 : 0,
    });
    renderTable();
    updateBadges(user);
  };

  /* ── delete task ── */
  window.deleteTask = function (id) {
    const t = Store.getTasks(user.id).find(function (t) {
      return t.id === +id;
    });
    confirmDialog(
      'Delete task "<strong>' + (t ? t.title : "") + '</strong>"?',
    ).then(function (ok) {
      if (!ok) return;
      Store.deleteTask(+id);
      toast("Task deleted", "danger");
      renderTable();
      updateBadges(user);
    });
  };

  /* ── filter pills ── */
  window.filterByStatus = function (status, btn) {
    fStatus = status;
    document.querySelectorAll(".summary-pill").forEach(function (p) {
      p.classList.remove("active");
    });
    if (btn) btn.classList.add("active");
    renderTable();
  };

  /* ── search bar ── */
  window.filterTasks = function (q) {
    fQuery = q.toLowerCase();
    renderTable();
  };

  /* ── dropdown filters ── */
  window.applyFilters = function () {
    fStatus = document.getElementById("filter-status")?.value || "";
    fPriority = document.getElementById("filter-priority")?.value || "";
    renderTable();
  };

  /* auto-priority when deadline changes */
  const deadlineInput = document.getElementById("t-deadline");
  if (deadlineInput) {
    deadlineInput.addEventListener("change", function () {
      const sel = document.getElementById("t-priority");
      if (sel) sel.value = autoPriority(this.value);
    });
  }

  renderTable();
}

/* =========================================================
   8.  EXAMS PAGE  –  examens.html
   ========================================================= */
function initExams() {
  const user = requireAuth("student");
  if (!user) return;
  fillSidebar(user);
  updateBadges(user);

  let editingId = null;
  let fQuery = "";

  /* ── fill subject dropdown ── */
  function fillSubjectSelect() {
    const sel = document.getElementById("e-subject");
    if (!sel) return;
    sel.innerHTML =
      '<option value="">Select subject</option>' +
      Store.getSubjects(user.id)
        .map(function (s) {
          return '<option value="' + s.id + '">' + s.name + "</option>";
        })
        .join("");
  }

  /* ── render exam list ── */
  function renderExams() {
    const list = document.getElementById("exams-list");
    if (!list) return;

    let exams = Store.getExams(user.id);
    if (fQuery) {
      exams = exams.filter(function (e) {
        return subjectName(e.subjectId).toLowerCase().includes(fQuery);
      });
    }

    const upcoming = exams
      .filter(function (e) {
        return daysUntil(e.date) >= 0;
      })
      .sort(function (a, b) {
        return new Date(a.date) - new Date(b.date);
      });

    const past = exams
      .filter(function (e) {
        return daysUntil(e.date) < 0;
      })
      .sort(function (a, b) {
        return new Date(b.date) - new Date(a.date);
      });

    list.innerHTML = "";

    /* upcoming section */
    list.insertAdjacentHTML(
      "beforeend",
      '<div class="timeline-section-label">Upcoming</div>',
    );
    if (!upcoming.length) {
      list.insertAdjacentHTML(
        "beforeend",
        '<p style="color:var(--muted);font-size:.85rem;padding:10px 0">No upcoming exams.</p>',
      );
    } else {
      upcoming.forEach(function (exam) {
        list.insertAdjacentHTML("beforeend", buildExamCard(exam, false));
      });
    }

    /* past section */
    list.insertAdjacentHTML(
      "beforeend",
      '<div class="timeline-section-label" style="margin-top:28px">Past exams</div>',
    );
    if (!past.length) {
      list.insertAdjacentHTML(
        "beforeend",
        '<p style="color:var(--muted);font-size:.85rem;padding:10px 0">No past exams.</p>',
      );
    } else {
      past.forEach(function (exam) {
        list.insertAdjacentHTML("beforeend", buildExamCard(exam, true));
      });
    }

    renderAside(upcoming[0]);
  }

  /* ── build one exam card HTML ── */
  function buildExamCard(exam, isPast) {
    const subj = subjectById(exam.subjectId);
    const days = daysUntil(exam.date);

    let countdown = "";
    if (!isPast) {
      if (days <= 5) {
        countdown =
          '<div class="exam-countdown"><i class="fas fa-fire"></i> In <strong>' +
          days +
          " day" +
          (days !== 1 ? "s" : "") +
          "</strong> — prepare now!</div>";
      } else {
        countdown =
          '<div class="exam-countdown moderate"><i class="far fa-calendar"></i> In <strong>' +
          days +
          " day" +
          (days !== 1 ? "s" : "") +
          "</strong></div>";
      }
    } else {
      countdown =
        '<div class="exam-countdown past-label"><i class="fas fa-check-circle"></i> Completed</div>';
    }

    const badge = !isPast
      ? days <= 5
        ? '<span class="badge badge-danger mb-8">Urgent</span>'
        : '<span class="badge badge-warning mb-8">Soon</span>'
      : '<span class="badge badge-muted mb-8">Past</span>';

    return (
      '<div class="exam-card ' +
      (isPast ? "past" : "upcoming") +
      '" data-subject="' +
      (subj ? subj.name.toLowerCase() : "") +
      '">' +
      '<div class="exam-card-left">' +
      '<div class="exam-big-date' +
      (isPast ? " past-date" : "") +
      '">' +
      '<div class="ebd-day">' +
      fmtDay(exam.date) +
      "</div>" +
      '<div class="ebd-month">' +
      fmtMonth(exam.date) +
      "</div>" +
      '<div class="ebd-year">' +
      fmtYear(exam.date) +
      "</div>" +
      "</div>" +
      "</div>" +
      '<div class="exam-card-body">' +
      '<div class="exam-card-subject"' +
      (isPast ? ' style="opacity:.65"' : "") +
      ">" +
      (subj ? subj.name : "—") +
      "</div>" +
      '<div class="exam-card-details">' +
      '<span><i class="fas fa-map-marker-alt"></i> ' +
      exam.room +
      "</span>" +
      '<span><i class="far fa-clock"></i> ' +
      exam.time +
      (exam.duration ? " – " + exam.duration : "") +
      "</span>" +
      '<span><i class="fas fa-hourglass-half"></i> ' +
      (exam.duration || "2 hours") +
      "</span>" +
      "</div>" +
      countdown +
      "</div>" +
      '<div class="exam-card-actions">' +
      badge +
      '<button class="btn btn-ghost btn-icon btn-sm" data-tip="Edit"   onclick="openModal(' +
      exam.id +
      ')"><i class="fas fa-pen"></i></button>' +
      '<button class="btn btn-danger btn-icon btn-sm" data-tip="Delete" onclick="deleteExam(' +
      exam.id +
      ')"><i class="fas fa-trash"></i></button>' +
      "</div>" +
      "</div>"
    );
  }

  /* ── render aside (countdown + schedule) ── */
  function renderAside(nextExam) {
    const subj = nextExam ? subjectById(nextExam.subjectId) : null;
    const subEl = document.querySelector(".next-exam-subject");
    const dtEl = document.querySelector(".next-exam-detail");
    if (subEl) subEl.textContent = subj ? subj.name : "No upcoming exams";
    if (dtEl && nextExam)
      dtEl.innerHTML =
        '<i class="fas fa-map-marker-alt"></i> ' +
        nextExam.room +
        " &nbsp;·&nbsp; " +
        nextExam.time;

    /* schedule list */
    const schedList = document.querySelector(".schedule-list");
    if (schedList) {
      const all = Store.getExams(user.id).sort(function (a, b) {
        return new Date(a.date) - new Date(b.date);
      });
      schedList.innerHTML = all
        .map(function (e) {
          const s = subjectById(e.subjectId);
          const past = daysUntil(e.date) < 0;
          const dot = past
            ? "muted"
            : daysUntil(e.date) <= 5
              ? "danger"
              : "warning";
          return (
            '<div class="schedule-item' +
            (past ? " past-sched" : "") +
            '">' +
            '<div class="si-dot ' +
            dot +
            '"></div>' +
            '<div class="si-info">' +
            '<div class="si-subject">' +
            (s ? s.name : "—") +
            "</div>" +
            '<div class="si-date">' +
            fmtMonth(e.date).slice(0, 3) +
            " " +
            fmtDay(e.date) +
            " · " +
            e.room +
            " · " +
            e.time +
            "</div>" +
            "</div>" +
            "</div>"
          );
        })
        .join("");
    }

    /* live countdown ticker */
    clearInterval(window._examTimer);
    function tick() {
      const el = document.getElementById("countdown");
      if (!el) return;
      if (!nextExam) {
        el.textContent = "—";
        return;
      }
      const diff =
        new Date(nextExam.date + "T" + nextExam.time + ":00") - new Date();
      if (diff <= 0) {
        el.textContent = "Happening now!";
        return;
      }
      const d = Math.floor(diff / 86400000);
      const h = Math.floor((diff % 86400000) / 3600000);
      const m = Math.floor((diff % 3600000) / 60000);
      el.innerHTML =
        "<span>" +
        d +
        "</span>d <span>" +
        h +
        "</span>h <span>" +
        m +
        "</span>m";
    }
    tick();
    window._examTimer = setInterval(tick, 60000);
  }

  /* ── open modal ── */
  window.openModal = function (id) {
    editingId = id ? +id : null;
    fillSubjectSelect();

    const titleEl = document.getElementById("exam-modal-title");
    if (editingId) {
      const e = Store.getExams(user.id).find(function (e) {
        return e.id === editingId;
      });
      if (!e) return;
      if (titleEl) titleEl.textContent = "Edit exam";
      setVal("e-subject", e.subjectId || "");
      setVal("e-date", e.date || "");
      setVal("e-time", e.time || "");
      setVal("e-room", e.room || "");
      setVal("e-notes", e.notes || "");
    } else {
      if (titleEl) titleEl.textContent = "Schedule exam";
      ["e-subject", "e-date", "e-time", "e-room", "e-notes"].forEach(
        function (id) {
          setVal(id, "");
        },
      );
    }
    openModalById("exam-modal");
  };

  window.closeModal = function () {
    closeModalById("exam-modal");
  };

  window.handleOverlayClick = function (e) {
    if (e.target === e.currentTarget) window.closeModal();
  };

  /* ── save exam (modal Save button) ── */
  const saveBtn = document.querySelector(
    "#exam-modal .modal-footer .btn-primary",
  );
  if (saveBtn) {
    saveBtn.onclick = function () {
      const subjId = parseInt(document.getElementById("e-subject")?.value);
      const date = document.getElementById("e-date")?.value || "";
      const time = document.getElementById("e-time")?.value || "";
      const room = (document.getElementById("e-room")?.value || "").trim();
      const notes = (document.getElementById("e-notes")?.value || "").trim();

      if (!subjId) {
        toast("Please select a subject", "warning");
        return;
      }
      if (!date) {
        toast("Please select a date", "warning");
        return;
      }
      if (!time) {
        toast("Please set a time", "warning");
        return;
      }

      if (editingId) {
        Store.updateExam(editingId, {
          subjectId: subjId,
          date: date,
          time: time,
          room: room,
          notes: notes,
        });
        toast("Exam updated", "success");
      } else {
        Store.addExam({
          subjectId: subjId,
          date: date,
          time: time,
          room: room,
          notes: notes,
          duration: "2 hours",
          userId: user.id,
        });
        toast("Exam scheduled", "success");
      }
      window.closeModal();
      renderExams();
      updateBadges(user);
    };
  }

  /* ── delete exam ── */
  window.deleteExam = function (id) {
    const e = Store.getExams(user.id).find(function (e) {
      return e.id === +id;
    });
    const s = subjectById(e ? e.subjectId : null);
    confirmDialog(
      "Delete the <strong>" +
        (s ? s.name : "—") +
        "</strong> exam on " +
        fmtDate(e ? e.date : null) +
        "?",
    ).then(function (ok) {
      if (!ok) return;
      Store.deleteExam(+id);
      toast("Exam deleted", "danger");
      renderExams();
      updateBadges(user);
    });
  };

  /* ── search ── */
  window.filterExams = function (q) {
    fQuery = q.toLowerCase();
    renderExams();
  };

  renderExams();
}

/* =========================================================
   9.  SETTINGS  –  student_settings.html  &  teacher_settings.html
   ========================================================= */
function initSettings() {
  const user = requireAuth();
  if (!user) return;
  fillSidebar(user);
  updateBadges(user);

  /* pre-fill profile */
  const parts = user.name.split(" ");
  setVal("firstname", parts[0] || "");
  setVal("lastname", parts.slice(1).join(" ") || "");
  setVal("email", user.email || "");
  setVal("department", user.department || "Computer Science");
  setVal(
    "grading-scale",
    user.teachingPrefs ? user.teachingPrefs.gradingScale : "Points (0-20)",
  );

  /* avatar */
  document.querySelectorAll(".avatar-preview").forEach(function (el) {
    el.textContent = user.avatar || user.name[0];
  });

  /* notification toggles */
  const n = user.notifications || {};
  setChecked("exam-notif", n.exams !== undefined ? n.exams : true);
  setChecked("task-notif", n.tasks !== undefined ? n.tasks : true);
  setChecked("summary-notif", n.summary !== undefined ? n.summary : false);
  setChecked(
    "submission-notif",
    n.submissions !== undefined ? n.submissions : true,
  );
  setChecked(
    "auto-average",
    user.teachingPrefs ? user.teachingPrefs.autoAverage : true,
  );

  /* ── save all ── */
  window.saveAllSettings = function () {
    const first = (document.getElementById("firstname")?.value || "").trim();
    const last = (document.getElementById("lastname")?.value || "").trim();
    const email = (document.getElementById("email")?.value || "").trim();
    if (!first || !last || !email) {
      toast("Please fill all required fields", "warning");
      return;
    }

    const patch = {
      name: first + " " + last,
      email: email,
      avatar: first[0].toUpperCase(),
      notifications: {
        exams: getChecked("exam-notif"),
        tasks: getChecked("task-notif"),
        summary: getChecked("summary-notif"),
        submissions: getChecked("submission-notif"),
      },
    };

    const dept = document.getElementById("department")?.value;
    if (dept) patch.department = dept;

    const gs = document.getElementById("grading-scale")?.value;
    if (gs)
      patch.teachingPrefs = {
        gradingScale: gs,
        autoAverage: getChecked("auto-average"),
      };

    Store.saveSettings(user.id, patch);
    fillSidebar(Store.me());
    toast("Settings saved!", "success");
  };

  /* ── change password ── */
  window.changePassword = function () {
    const curr = document.getElementById("current-pw")?.value || "";
    const nw = document.getElementById("new-pw")?.value || "";
    const conf = document.getElementById("confirm-pw")?.value || "";
    if (!curr) {
      toast("Enter your current password", "warning");
      return;
    }
    if (curr !== user.password) {
      toast("Current password is incorrect", "danger");
      return;
    }
    if (nw.length < 8) {
      toast("New password must be ≥ 8 characters", "warning");
      return;
    }
    if (nw !== conf) {
      toast("Passwords do not match", "danger");
      return;
    }
    Store.saveSettings(user.id, { password: nw });
    setVal("current-pw", "");
    setVal("new-pw", "");
    setVal("confirm-pw", "");
    toast("Password updated!", "success");
  };
}

/* =========================================================
   10. TEACHER DASHBOARD  –  teacher-dashboard.html
   ========================================================= */
function initTeacherDashboard() {
  const user = requireAuth("teacher");
  if (!user) return;
  fillSidebar(user);

  const greetEl = document.querySelector(".welcome-text h2");
  if (greetEl) {
    const h = new Date().getHours();
    const period = h < 12 ? "morning" : h < 18 ? "afternoon" : "evening";
    greetEl.innerHTML =
      "Good " +
      period +
      ", Dr. " +
      user.name.split(" ").pop() +
      ' <span style="color:var(--gold)">✦</span>';
  }
}

/* =========================================================
   11. TEACHER CLASSES  –  teacher-classes.html
   ========================================================= */
function initTeacherClasses() {
  requireAuth("teacher");

  window.openModal = function () {
    document.getElementById("modal-title-text").textContent =
      "Create New Class";
    openModalById("modal-overlay");
  };
  window.openEditModal = function (name) {
    document.getElementById("modal-title-text").textContent =
      "Edit Class — " + name;
    openModalById("modal-overlay");
  };
  window.closeModal = function () {
    closeModalById("modal-overlay");
  };
  window.saveClass = function () {
    window.closeModal();
    toast("Class saved!", "success");
  };
  window.deleteClass = function (btn) {
    const card = btn.closest(".class-card");
    const name = card
      ? card.querySelector(".class-name")?.textContent || "this class"
      : "this class";
    confirmDialog(
      "Delete <strong>" + name + "</strong>? This cannot be undone.",
    ).then(function (ok) {
      if (!ok) return;
      if (card) {
        card.style.transition = "opacity .3s, transform .3s";
        card.style.opacity = "0";
        card.style.transform = "scale(.9)";
        setTimeout(function () {
          card.remove();
        }, 320);
      }
      toast("Class deleted", "danger");
    });
  };
  window.selectColor = function (dot) {
    document.querySelectorAll(".color-dot").forEach(function (d) {
      d.classList.remove("selected");
    });
    dot.classList.add("selected");
  };
  window.filterClasses = function (q) {
    document.querySelectorAll(".class-card[data-name]").forEach(function (c) {
      c.style.display = c.dataset.name.toLowerCase().includes(q.toLowerCase())
        ? ""
        : "none";
    });
  };

  const ov = document.getElementById("modal-overlay");
  if (ov)
    ov.addEventListener("click", function (e) {
      if (e.target === ov) window.closeModal();
    });
}

/* =========================================================
   12. TEACHER GRADES  –  teacher-grades.html
   ========================================================= */
function initTeacherGrades() {
  requireAuth("teacher");

  const STUDENTS = [
    { id: 1, name: "Ahmed Ben Ali", init: "A" },
    { id: 2, name: "Yassine Boukhari", init: "Y" },
    { id: 3, name: "Amina Khalil", init: "A" },
    { id: 4, name: "Sarra Hamdi", init: "S" },
    { id: 5, name: "Mounir Trabelsi", init: "M" },
    { id: 6, name: "Rania Kchaou", init: "R" },
    { id: 7, name: "Bilel Gharbi", init: "B" },
    { id: 8, name: "Ines Turki", init: "I" },
    { id: 9, name: "Hatem Karray", init: "H" },
    { id: 10, name: "Nour Bousbia", init: "N" },
    { id: 11, name: "Skander Mejri", init: "S" },
    { id: 12, name: "Leila Ayari", init: "L" },
  ];

  let currentMax = 20;

  function letterGrade(score, max) {
    const p = (score / max) * 20;
    if (p >= 16) return "A";
    if (p >= 13) return "B";
    if (p >= 10) return "C";
    if (p >= 7) return "D";
    return "F";
  }
  function letterClass(l) {
    return { A: "A", B: "B", C: "C", D: "D", F: "F" }[l] || "na";
  }

  function renderGradeTable() {
    const tbody = document.getElementById("grades-body");
    if (!tbody) return;
    tbody.innerHTML = STUDENTS.map(function (s, i) {
      const score = i < 7 ? Math.floor(Math.random() * 8 + 12) : "";
      const letter = score !== "" ? letterGrade(score, currentMax) : "—";
      const lc = score !== "" ? letterClass(letter) : "na";
      return (
        '<tr id="row-' +
        s.id +
        '">' +
        "<td>" +
        '<div style="display:flex;align-items:center;gap:10px">' +
        "<div style=\"width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,var(--gold-dim),var(--gold));display:flex;align-items:center;justify-content:center;font-family:'Playfair Display',serif;font-size:.75rem;color:var(--navy);font-weight:700\">" +
        s.init +
        "</div>" +
        s.name +
        "</div>" +
        "</td>" +
        '<td><input class="grade-input' +
        (score !== "" ? " saved" : "") +
        '" type="number" min="0" max="' +
        currentMax +
        '" value="' +
        score +
        '" id="grade-' +
        s.id +
        '" onchange="updateLetter(' +
        s.id +
        "," +
        currentMax +
        ')" oninput="this.classList.remove(\'saved\')"/></td>' +
        '<td style="color:var(--muted);font-size:.82rem">/ ' +
        currentMax +
        "</td>" +
        '<td><span class="grade-letter ' +
        lc +
        '" id="letter-' +
        s.id +
        '">' +
        letter +
        "</span></td>" +
        '<td><button class="btn btn-icon btn-ghost" data-tip="Add feedback" onclick="openFeedback(' +
        s.id +
        ",'" +
        s.name +
        '\')"><i class="fas fa-comment-alt"></i></button></td>' +
        '<td><button class="save-row-btn" data-tip="Save" onclick="saveRow(' +
        s.id +
        ')"><i class="fas fa-check"></i></button></td>' +
        "</tr>"
      );
    }).join("");
  }

  window.updateLetter = function (id, max) {
    const val = parseFloat(document.getElementById("grade-" + id)?.value);
    const el = document.getElementById("letter-" + id);
    if (!el) return;
    if (isNaN(val)) {
      el.textContent = "—";
      el.className = "grade-letter na";
      return;
    }
    const l = letterGrade(val, max);
    el.textContent = l;
    el.className = "grade-letter " + letterClass(l);
  };

  window.saveRow = function (id) {
    const inp = document.getElementById("grade-" + id);
    if (inp) inp.classList.add("saved");
    toast("Grade saved", "success");
  };

  window.saveAllGrades = function () {
    document.querySelectorAll(".grade-input").forEach(function (inp) {
      inp.classList.add("saved");
    });
    toast("All grades saved", "success");
  };

  window.publishGrades = function () {
    window.saveAllGrades();
    toast("Grades published! Students notified.", "info");
  };

  window.fillAbsent = function () {
    document.querySelectorAll(".grade-input").forEach(function (inp, i) {
      if (inp.value === "") {
        inp.value = 0;
        window.updateLetter(STUDENTS[i].id, currentMax);
      }
    });
  };

  window.sortByGrade = function () {
    const tbody = document.getElementById("grades-body");
    if (!tbody) return;
    const rows = Array.from(tbody.querySelectorAll("tr"));
    rows.sort(function (a, b) {
      const va = parseFloat(a.querySelector(".grade-input")?.value) || 0;
      const vb = parseFloat(b.querySelector(".grade-input")?.value) || 0;
      return vb - va;
    });
    rows.forEach(function (r) {
      tbody.appendChild(r);
    });
  };

  window.selectAssignment = function (el, title, max) {
    document.querySelectorAll(".assignment-item").forEach(function (i) {
      i.classList.remove("active");
    });
    el.classList.add("active");
    const titleEl = document.getElementById("assignment-title");
    const maxEl = document.getElementById("max-pts");
    if (titleEl) titleEl.textContent = title;
    if (maxEl) maxEl.textContent = max;
    currentMax = parseInt(max);
    renderGradeTable();
  };

  window.openFeedback = function (id, name) {
    const nameEl = document.getElementById("feedback-student-name");
    const textEl = document.getElementById("feedback-text");
    if (nameEl) nameEl.textContent = name;
    if (textEl) textEl.value = "";
    openModalById("modal-overlay");
  };
  window.saveFeedback = function () {
    closeModalById("modal-overlay");
    toast("Feedback saved", "success");
  };
  window.closeModal = function () {
    closeModalById("modal-overlay");
  };

  const ov = document.getElementById("modal-overlay");
  if (ov)
    ov.addEventListener("click", function (e) {
      if (e.target === ov) window.closeModal();
    });

  renderGradeTable();
}

/* =========================================================
   13. TEACHER STUDENTS  –  teacher-students.html
   ========================================================= */
function initTeacherStudents() {
  requireAuth("teacher");

  window.openModal = function () {
    document.getElementById("modal-title-text").textContent = "Add Student";
    /* restore default add-student body */
    const body = document.getElementById("modal-body-content");
    if (body) {
      body.innerHTML =
        '<div class="grid-2">' +
        '<div class="form-group"><label class="form-label">First Name</label><input class="form-control" type="text" placeholder="First name"/></div>' +
        '<div class="form-group"><label class="form-label">Last Name</label><input class="form-control" type="text" placeholder="Last name"/></div>' +
        '<div class="form-group"><label class="form-label">Student ID</label><input class="form-control" type="text" placeholder="e.g. 22INF045"/></div>' +
        '<div class="form-group"><label class="form-label">Email</label><input class="form-control" type="email" placeholder="student@univ.edu"/></div>' +
        '<div class="form-group" style="grid-column:1/-1"><label class="form-label">Enroll in Classes</label>' +
        '<div style="display:flex;flex-direction:column;gap:8px;margin-top:4px">' +
        '<label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:.875rem;color:var(--muted-less)"><input type="checkbox"/> MATH-301</label>' +
        '<label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:.875rem;color:var(--muted-less)"><input type="checkbox"/> PHYS-210</label>' +
        '<label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:.875rem;color:var(--muted-less)"><input type="checkbox"/> CS-402</label>' +
        "</div>" +
        "</div>" +
        "</div>";
    }
    openModalById("modal-overlay");
  };

  window.openStudentProfile = function (name) {
    document.getElementById("modal-title-text").textContent =
      "Student Profile — " + name;
    const body = document.getElementById("modal-body-content");
    if (body) {
      body.innerHTML =
        '<div style="text-align:center;padding:8px 0 16px">' +
        "<div style=\"width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,var(--gold-dim),var(--gold));display:flex;align-items:center;justify-content:center;font-family:'Playfair Display',serif;font-size:1.6rem;color:var(--navy);font-weight:700;margin:0 auto 12px\">" +
        name[0] +
        "</div>" +
        "<div style=\"font-family:'Playfair Display',serif;font-size:1.1rem;color:var(--white)\">" +
        name +
        "</div>" +
        '<div style="font-size:.75rem;color:var(--muted);margin-top:4px">Active student · 3 classes enrolled</div>' +
        "</div>" +
        '<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:8px">' +
        '<div style="background:rgba(255,255,255,.04);border:1px solid var(--border-dim);border-radius:var(--radius-sm);padding:12px;text-align:center"><div style="font-family:\'Playfair Display\',serif;font-size:1.3rem;color:var(--white);font-weight:700">14.8</div><div style="font-size:.68rem;color:var(--muted);text-transform:uppercase;letter-spacing:.07em;margin-top:2px">Average</div></div>' +
        '<div style="background:rgba(255,255,255,.04);border:1px solid var(--border-dim);border-radius:var(--radius-sm);padding:12px;text-align:center"><div style="font-family:\'Playfair Display\',serif;font-size:1.3rem;color:var(--white);font-weight:700">7/8</div><div style="font-size:.68rem;color:var(--muted);text-transform:uppercase;letter-spacing:.07em;margin-top:2px">Submitted</div></div>' +
        '<div style="background:rgba(255,255,255,.04);border:1px solid var(--border-dim);border-radius:var(--radius-sm);padding:12px;text-align:center"><div style="font-family:\'Playfair Display\',serif;font-size:1.3rem;color:#82b8f0;font-weight:700">B</div><div style="font-size:.68rem;color:var(--muted);text-transform:uppercase;letter-spacing:.07em;margin-top:2px">Grade</div></div>' +
        "</div>" +
        '<div class="form-group"><label class="form-label">Note for student</label><textarea class="form-control" placeholder="Add a note…"></textarea></div>';
    }
    openModalById("modal-overlay");
  };

  window.closeModal = function () {
    closeModalById("modal-overlay");
  };

  window.filterStudents = function (q) {
    document.querySelectorAll(".student-card").forEach(function (c) {
      c.style.display = c.dataset.name.toLowerCase().includes(q.toLowerCase())
        ? ""
        : "none";
    });
  };

  window.setView = function (v) {
    document
      .getElementById("btn-grid")
      ?.classList.toggle("active", v === "grid");
    document
      .getElementById("btn-table")
      ?.classList.toggle("active", v === "table");
  };

  const ov = document.getElementById("modal-overlay");
  if (ov)
    ov.addEventListener("click", function (e) {
      if (e.target === ov) window.closeModal();
    });
}

/* =========================================================
   14. TEACHER ASSIGNMENTS  –  teacher-tasks.html
   ========================================================= */
function initTeacherTasks() {
  requireAuth("teacher");

  window.openModal = function () {
    document.getElementById("modal-title-text").textContent = "New Assignment";
    openModalById("modal-overlay");
  };
  window.openEditModal = function (name) {
    document.getElementById("modal-title-text").textContent = "Edit — " + name;
    openModalById("modal-overlay");
  };
  window.closeModal = function () {
    closeModalById("modal-overlay");
  };
  window.saveTask = function () {
    window.closeModal();
    toast("Assignment saved!", "success");
  };

  window.filterTasks = function (q) {
    document
      .querySelectorAll("#tasks-body tr[data-name]")
      .forEach(function (row) {
        row.style.display = row.dataset.name
          .toLowerCase()
          .includes(q.toLowerCase())
          ? ""
          : "none";
      });
  };

  window.filterByStatus = function (val) {
    document.querySelectorAll("#tasks-body tr").forEach(function (row) {
      if (!val) {
        row.style.display = "";
        return;
      }
      row.style.display = row.dataset.status === val ? "" : "none";
    });
  };

  window.switchTab = function (btn, type) {
    document.querySelectorAll(".tab-btn").forEach(function (b) {
      b.classList.remove("active");
    });
    btn.classList.add("active");
    window.filterByStatus(
      type === "all" ? "" : type === "pending" ? "open" : type,
    );
  };

  const ov = document.getElementById("modal-overlay");
  if (ov)
    ov.addEventListener("click", function (e) {
      if (e.target === ov) window.closeModal();
    });
}

/* =========================================================
   15. LOGOUT  –  every page
   ========================================================= */
function initLogout() {
  document.querySelectorAll(".btn-logout").forEach(function (el) {
    el.addEventListener("click", function (e) {
      e.preventDefault();
      Store.logout();
      location.href = "login.html";
    });
  });
}

/* =========================================================
   16. ROUTER  –  runs on every page load
   ========================================================= */
document.addEventListener("DOMContentLoaded", function () {
  /* always available on every page */
  window.toggleSidebar = toggleSidebar;
  initLogout();

  const page = location.pathname.split("/").pop() || "login.html";

  if (page === "login.html" || page === "") initLogin();
  if (page === "register.html") initRegister();
  if (page === "dashboard.html") initDashboard();
  if (page === "matieres.html") initSubjects();
  if (page === "taches.html") initTasks();
  if (page === "examens.html") initExams();
  if (page === "student_settings.html") initSettings();
  if (page === "teacher_settings.html") initSettings();
  if (page === "teacher-dashboard.html") initTeacherDashboard();
  if (page === "teacher-classes.html") initTeacherClasses();
  if (page === "teacher-grades.html") initTeacherGrades();
  if (page === "teacher-students.html") initTeacherStudents();
  if (page === "teacher-tasks.html") initTeacherTasks();
});

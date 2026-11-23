# User Guide

This guide explains how to use University Assistant day to day, for each of the three roles: **administrator**, **teacher** and **student**.

If the app is not running yet, follow the [Quick start in the README](../README.md#quick-start-docker) first. All the examples below assume the app is at **http://localhost:8080** with the demo data loaded.

---

## Contents

1. [How accounts work](#1-how-accounts-work)
2. [Setting up a new semester (step by step)](#2-setting-up-a-new-semester-step-by-step)
3. [Administrator guide](#3-administrator-guide)
4. [Teacher guide](#4-teacher-guide)
5. [Student guide](#5-student-guide)
6. [Demo accounts](#6-demo-accounts)
7. [FAQ](#7-faq)
8. [Current limitations](#8-current-limitations)

---

## 1. How accounts work

Registration is **invite-only**. Nobody can create an account unless an administrator has first added their e-mail address to the **whitelist**. The whitelist entry decides:

- the **role** the person gets: student, teacher or admin;
- for students, the **class** they join, using the class code (for example `GL2`).

```
Admin adds e-mail to whitelist ──► Person registers at /register ──► Person signs in at /login
         (role + class)               (name + password)                (lands on their dashboard)
```

After signing in, each person lands on the dashboard for their role:

| Role | Area | Starts at |
|------|------|-----------|
| Student | `/student/…` | Student dashboard |
| Teacher | `/teacher/…` | Teacher dashboard |
| Admin | `/admin/…` | Admin dashboard |

Each person can only open their own area. A student who opens a `/teacher` page gets "Access denied". An admin also has teacher and student rights.

**The first administrator** is created automatically by Docker from `ADMIN_EMAIL` / `ADMIN_PASSWORD`. Without Docker, create it with:

```bash
php bin/console app:create-admin you@university.tn
```

---

## 2. Setting up a new semester (step by step)

This is the order that works. Students need an existing class to join, so the class has to exist before they register.

| Step | Who | Where | What |
|------|-----|-------|------|
| 1 | Admin | Admin → **Whitelist** | Add each teacher's e-mail with role **Professeur** (teacher) and, optionally, their department. |
| 2 | Teacher | `/register` | Register with that e-mail and choose a password. |
| 3 | Teacher | Teacher → **My Classes** → *New Class* | Create the class and give it a **code**, for example `GL2-A`. |
| 4 | Admin | Admin → **Whitelist** | Add each student's e-mail with role **Étudiant** (student) and the class code from step 3 in the **Class** field. |
| 5 | Students | `/register` | Register. Each student is placed in their class automatically. |
| 6 | Teacher | Teacher → **Assignments** → *New Assignment* | Post work for the class. Students see it straight away. |

> **Tip:** class codes are matched loosely: upper/lower case and separators don't matter, so `gl2 a` matches `GL2-A`. The code has to exist, though, or the student's registration is refused with "No class matches …".

---

## 3. Administrator guide

Sign in as an admin (demo: `admin@example.com` / `ChangeMe123!`).

### Dashboard — `/admin/dashboard`
Shows the number of students, teachers, suspended accounts and whitelisted e-mails.

### Whitelist — `/admin/whitelist`
This is where you control who can join.

**Add an e-mail**
1. Type the e-mail address.
2. Choose the role: **Étudiant** (student), **Professeur** (teacher) or **Admin**.
3. For a teacher, you can enter a **department** (for example *Informatique*).
4. For a student, enter the **class code** (for example `GL2`). This is required: a student without a class cannot register.
5. Click **Ajouter l'Email** (Add e-mail).

Each entry shows whether the person has already registered.

**Revoke access.** Click the delete button on an entry. If the person had already registered, **their account is deleted too**, together with their submissions, grades and notifications. Use it with care.

### Users — `/admin/users`
Lists every account. Search by name or e-mail, and filter by role.

Accounts marked **Suspendu** (suspended) cannot sign in. They see *"Your account has been suspended. Please contact an administrator."* There is no suspend button in the interface yet. To suspend an account from the database:

```bash
docker compose exec database mysql -uapp -papp_password assist \
  -e "UPDATE user SET is_active = 0 WHERE email = 'someone@example.com'"
```

Use `is_active = 1` to restore access.

---

## 4. Teacher guide

Sign in as a teacher (demo: `teacher@demo.local` / `Demo1234!`).

### Dashboard — `/teacher/dashboard`
An overview of your classes, open assignments and upcoming exams.

### My Classes — `/teacher/classes`
Every class you teach, with its number of students, number of assignments and average grade.

**Create a class:** click *New Class* and fill in:

| Field | Required | Notes |
|-------|----------|-------|
| Name | ✔ | for example *Advanced Mathematics* |
| Code | ✔ | Unique, for example `MATH-301`. **Give this code to the admin**, who uses it to whitelist your students. |
| Semester | ✔ | Semester 1 – 4 |
| Category | ✔ | Mathematics, Physics, Computer Science, Chemistry or English. It sets the card's colour and icon. |
| Max students, schedule, description | | Shown on the class card |

**Delete a class:** use the delete button on the card. This also deletes the class's subjects, assignments, exams and grades. You can only delete a class that no other teacher teaches in.

### Assignments — `/teacher/tasks`
All assignments across your classes, with deadline, priority and **submission rate**.

**Create an assignment:** click *New Assignment* and fill in the title, class, deadline, priority (High / Medium / Low), and optionally a description and max points (20 by default).

Deadlines are colour-coded: red when overdue, orange when due within 3 days, green otherwise. Use the search box and the status and class filters to find an assignment. Delete one with its delete button.

### Grades — `/teacher/grades`
Choose a class, then an assignment, to see each student's grade (out of 20), letter grade (A–F) and feedback, with the class average, highest and lowest grade.

### Students — `/teacher/students`
Every student in your classes. Search by name and filter by class.

### Settings — `/teacher/settings`
Change your name, e-mail and password. The new password must be at least 8 characters.

---

## 5. Student guide

Sign in as a student (demo: `student@demo.local` / `Demo1234!`).

### Dashboard — `/student/dashboard`
Your day at a glance:
- **urgent tasks**: high-priority assignments that are still open;
- **upcoming exams**;
- your overall progress.

The sidebar badges show how many urgent tasks and upcoming exams you have, on every page.

### Subjects — `/student/subjects`
The subjects of your class, with their teacher and **coefficient**. The coefficient is how much the subject weighs in your average.

### Tasks — `/student/tasks`
Every assignment from your subjects, sorted by deadline. Use the filters to show:
- **To do** or **Done**;
- **High**, **Medium** or **Low** priority.

Deadlines turn orange when they are close and red when they have passed.

### Exams — `/student/exams`
- A **countdown** to your next exam.
- **Upcoming exams** with date, time, room and duration. Exams less than 7 days away are marked **Urgent**.
- **Past exams**.

### Grades — `/student/grades` (*Mes Notes*)
Your published grades, with the teacher's feedback, and:
- your **overall average**, weighted by subject coefficients;
- the number of grades below 10/20.

A grade only appears here once the teacher has published it.

### Settings — `/student/settings`
Change your name, e-mail and password, and see your student ID and class.

---

## 6. Demo accounts

These are created by `app:seed-demo`, which runs automatically in development mode.

| Role | E-mail | Password | Has |
|------|--------|----------|-----|
| Admin | `admin@example.com` | `ChangeMe123!` | Full access |
| Teacher | `teacher@demo.local` | `Demo1234!` | Algorithms and Databases in GL2, Computer Networks in RT2 |
| Teacher | `teacher2@demo.local` | `Demo1234!` | Mathematics in GL2, Probability in RT2 |
| Student | `student@demo.local` | `Demo1234!` | Class GL2, with grades, tasks and exams |
| Students | `student2@` … `student4@demo.local` | `Demo1234!` | Class GL2 |
| Students | `student5@`, `student6@demo.local` | `Demo1234!` | Class RT2 |
| Invite | `newstudent@demo.local` | *you choose* | Whitelisted for GL2 but not registered yet, so you can try `/register` |
| Invite | `newteacher@demo.local` | *you choose* | Whitelisted as a teacher but not registered yet |

Demo dates are relative to the day the data was loaded, so there are always overdue, urgent and upcoming items. To reload fresh demo data, reset everything with `docker compose down -v && docker compose up -d`.

---

## 7. FAQ

**I forgot my password.** There is no self-service reset yet. An admin can set a new one from the command line:

```bash
docker compose exec app php bin/console security:hash-password 'NewPassword123'
docker compose exec database mysql -uapp -papp_password assist \
  -e "UPDATE user SET password = '<hash from the previous command>' WHERE email = 'someone@example.com'"
```

**"This email address is not authorized to register."** The e-mail isn't on the whitelist, or it is spelled differently. Ask an admin to add it.

**"An account already exists for this email address."** Someone has already registered with this e-mail. Sign in instead.

**"No class matches 'XYZ'."** The class code in your whitelist entry doesn't match any existing class. Ask the admin to check it against the code the teacher used.

**A teacher created a class but no students appear.** Students are attached to a class when they **register**. Make sure the admin whitelisted them with that class code, and that they registered afterwards.

**Can a student belong to several classes?** The database supports it, but registration attaches one class. Adding more currently requires editing the `user_classe` table.

---

## 8. Current limitations

These screens show parts of the interface whose saving is **not implemented yet**:

| Screen | What doesn't save yet |
|--------|-----------------------|
| Teacher → Grades | Editing a grade or feedback and the *Save* buttons. Grades are currently created by the demo seeder only. |
| Teacher → Students | The *Add student* form. Students join by registering. |
| Student → Subjects | The *Add subject* form. Subjects come from the class's teachers. |
| Student → Tasks | Ticking a task as done. Its status is set by the teacher. |
| Admin → Users | The edit and delete buttons on each user, and suspending an account. |
| Notifications | Notifications are stored (the demo creates some) but not displayed anywhere yet. |
| Exams | There is no screen to create exams yet. They are created by the demo seeder or directly in the `examen` table. |
| Accounts | Password reset by e-mail. |

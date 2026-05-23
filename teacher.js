/**
 * Fichier : teacher.js
 * Rôle : Gère l'interactivité globale du Front-Office (Espace Enseignant).
 * (Animations, Modales, Choix de couleurs, AJAX pour l'ajout de notes/devoirs)
 */

document.addEventListener('DOMContentLoaded', () => {
    
    // =====================================================================
    // 1. ANIMATIONS D'APPARITION (Staggered Animations)
    // Fait apparaître les cartes et tableaux en douceur
    // =====================================================================
    const animatedElements = document.querySelectorAll('[class*="anim-"]');
    animatedElements.forEach(el => {
        const match = el.className.match(/anim-(\d+)/);
        if (match) {
            const delay = parseInt(match[1]) * 100;
            el.style.animationDelay = `${delay}ms`;
            el.classList.add('fade-in-up'); 
        }
    });

    // =====================================================================
    // 2. ANIMATION DES BARRES DE PROGRESSION
    // Permet à la barre de se remplir depuis 0% au chargement de la page
    // =====================================================================
    const progressBars = document.querySelectorAll('.progress-bar, .submission-bar');
    progressBars.forEach(bar => {
        const targetWidth = bar.style.width; // Sauvegarde la vraie largeur
        bar.style.width = '0%';              // Met à 0 temporairement
        
        requestAnimationFrame(() => {
            setTimeout(() => {
                bar.style.width = targetWidth; // Applique l'animation
            }, 100);
        });
    });
});

// =========================================================================
// 3. GESTION DES MODALES (Pop-ups)
// =========================================================================

// Ouvre une modale (par défaut, cherche un élément avec class 'modal-overlay')
function openModal(modalId = 'modal-overlay') {
    const modal = document.getElementById(modalId);
    // Si l'ID n'est pas trouvé, on cherche le premier élément avec la classe .modal-overlay
    const target = modal || document.querySelector('.modal-overlay');
    if(target) target.classList.add('active');
}

// Ferme une modale
function closeModal(modalId = 'modal-overlay') {
    const modal = document.getElementById(modalId);
    const target = modal || document.querySelector('.modal-overlay.active');
    if(target) target.classList.remove('active');
}

// Ferme la modale si on clique à l'extérieur de la boîte de dialogue (sur le fond sombre)
function handleOverlayClick(e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('active');
    }
}

// =========================================================================
// 4. GESTION DE LA PAGE CLASSES (Choix de la couleur)
// =========================================================================

// Permet de sélectionner une couleur lors de la création d'une classe
function selectColor(element) {
    // Retire la classe 'selected' de toutes les pastilles de couleur
    document.querySelectorAll('.color-dot').forEach(dot => {
        dot.classList.remove('selected');
    });
    // Ajoute la classe 'selected' à la pastille cliquée
    element.classList.add('selected');
}

// Simulation d'enregistrement d'une nouvelle classe
function saveClass() {
    // TODO Membre 4 : Récupérer les champs (Nom, Code, Semestre, etc.) et envoyer via Fetch
    alert('Enregistrement de la classe en cours... (À lier au backend Symfony)');
    closeModal();
}

// =========================================================================
// 5. GESTION DE LA PAGE TASKS & GRADES (Devoirs et Notes)
// =========================================================================

// Enregistre un nouveau devoir / tâche
function saveTask() {
    // TODO Membre 4 : Appel Fetch POST /teacher/tasks/new
    alert('Devoir publié avec succès ! (À lier au backend Symfony)');
    closeModal();
}

// Ouvre la modale pour ajouter un commentaire à un étudiant (Page Grades)
function openFeedbackModal(studentName) {
    // Met à jour le nom de l'étudiant dans le titre de la modale
    const nameEl = document.getElementById('feedback-student-name');
    if (nameEl) nameEl.textContent = studentName;
    openModal('modal-overlay'); // Ouvre la modale de feedback
}

// Sauvegarde le feedback d'un étudiant
function saveFeedback() {
    const feedbackText = document.getElementById('feedback-text')?.value;
    // TODO Membre 4 : Appel Fetch POST /teacher/grades/feedback
    alert('Feedback enregistré pour cet étudiant !');
    closeModal();
}

// =========================================================================
// 6. GESTION DE LA PAGE SETTINGS (Paramètres Professeur)
// =========================================================================

async function changePassword() {
    const curr = document.getElementById('current-pw')?.value;
    const newPw = document.getElementById('new-pw')?.value;
    const confirm = document.getElementById('confirm-pw')?.value;

    if (!curr || !newPw || !confirm) {
        alert('Veuillez remplir tous les champs de mot de passe.');
        return;
    }
    if (newPw !== confirm) {
        alert('Le nouveau mot de passe et la confirmation ne correspondent pas.');
        return;
    }
    if (newPw.length < 8) {
        alert('Le nouveau mot de passe doit contenir au moins 8 caractères.');
        return;
    }

    // TODO Membre 4 : Implémenter la requête Fetch vers Symfony avec le token CSRF
    alert('Simulation : Mot de passe mis à jour !');
}

async function saveAllSettings() {
    // Récupération des préférences (Toggles)
    const emailNotif = document.getElementById('email-notif')?.checked;
    const digestNotif = document.getElementById('digest-notif')?.checked;
    const autoAverage = document.getElementById('auto-average')?.checked;

    const payload = {
        emailNotifications: emailNotif,
        dailyDigest: digestNotif,
        autoCalculateAverage: autoAverage
    };

    // TODO Membre 4 : Implémenter l'appel Fetch
    alert('Préférences sauvegardées !');
}
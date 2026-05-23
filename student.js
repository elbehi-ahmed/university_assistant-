/**
 * Fichier : student.js
 * Rôle : Gère l'interactivité globale du Front-Office (Espace Étudiant).
 * (Animations, Barres de progression, Modales, AJAX des tâches/paramètres)
 */

document.addEventListener('DOMContentLoaded', () => {
    
    // =====================================================================
    // 1. ANIMATIONS D'APPARITION (Staggered Animations)
    // Fait apparaître les cartes et tableaux en douceur (Dashboard, etc.)
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
    // 2. ANIMATION DES BARRES DE PROGRESSION (Notes & Dashboard)
    // Permet à la barre de se remplir depuis 0% au chargement de la page
    // =====================================================================
    const progressBars = document.querySelectorAll('.progress-bar, .note-bar');
    progressBars.forEach(bar => {
        const targetWidth = bar.style.width; // Sauvegarde la vraie largeur (ex: 75%)
        bar.style.width = '0%';              // Met à 0
        
        // Laisse le navigateur rendre la largeur 0, puis anime vers la cible
        requestAnimationFrame(() => {
            setTimeout(() => {
                bar.style.width = targetWidth;
            }, 100);
        });
    });
});

// =========================================================================
// 3. GESTION DES MODALES (Pop-ups pour Ajouter/Modifier Matières & Examens)
// =========================================================================

// Ouvre une modale spécifique (par défaut celle des matières)
function openModal(modalId = 'subject-modal') {
    const modal = document.getElementById(modalId);
    if(modal) modal.classList.add('active');
}

// Ferme une modale
function closeModal(modalId = 'subject-modal') {
    const modal = document.getElementById(modalId);
    if(modal) modal.classList.remove('active');
}

// Ouvre la modale de confirmation de suppression
function openDeleteModal(id, name) {
    // Insère le nom de l'élément (ex: "Mathématiques") dans le texte de la modale
    const nameSpan = document.getElementById('delete-name');
    if(nameSpan) nameSpan.textContent = name;
    
    // Attache l'ID au bouton de confirmation pour l'utiliser lors du clic
    const btnDanger = document.querySelector('#delete-modal .btn-danger');
    if(btnDanger) btnDanger.setAttribute('data-id', id);
    
    const modal = document.getElementById('delete-modal');
    if(modal) modal.classList.add('active');
}

// Ferme la modale de suppression
function closeDeleteModal() {
    const modal = document.getElementById('delete-modal');
    if(modal) modal.classList.remove('active');
}

// Ferme la modale si on clique à l'extérieur (sur le fond gris)
function handleOverlayClick(e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('active');
    }
}


// =========================================================================
// 4. GESTION DES TÂCHES & DEVOIRS (Filtres et AJAX)
// =========================================================================

// Filtre les tâches (Tout, À faire, Terminé, Urgent)
function filterTasks(status, btn) {
    // 1. Mise à jour de l'UI (Boutons)
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    // 2. Filtrage des lignes du tableau
    document.querySelectorAll('.task-row').forEach(row => {
        if (status === 'all') {
            row.style.display = '';
        } else if (status === 'urgent') {
            // Urgent si la priorité est 'high' ET qu'elle n'est pas encore terminée
            row.style.display = (row.dataset.priority === 'high' && row.dataset.status === 'todo') ? '' : 'none';
        } else {
            row.style.display = row.dataset.status === status ? '' : 'none';
        }
    });
}

// Coche / Décoche une tâche (Envoi AJAX au serveur)
async function toggleTask(checkbox, taskId) {
    const row = checkbox.closest('tr');
    const nameEl = row.querySelector('.task-name');
    const pill = row.querySelector('.status-pill');
    const isDone = checkbox.checked;

    // 1. Mise à jour visuelle immédiate (Optimistic UI)
    if (isDone) {
        if(nameEl) nameEl.classList.add('done-text');
        if(pill) { pill.className = 'status-pill done'; pill.textContent = 'Done'; }
        row.dataset.status = 'done';
    } else {
        if(nameEl) nameEl.classList.remove('done-text');
        if(pill) { pill.className = 'status-pill todo'; pill.textContent = 'To Do'; }
        row.dataset.status = 'todo';
    }

    // 2. Appel AJAX vers Symfony (TODO Membre 3)
    try {
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const token = csrfMeta ? csrfMeta.content : '';

        // Remplacer par la vraie route Symfony configurée par le Membre 3
        const response = await fetch(`/student/tasks/${taskId}/toggle`, {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'X-CSRF-Token': token 
            },
            body: JSON.stringify({ status: isDone ? 'done' : 'todo' })
        });

        if (!response.ok) {
            console.error('Erreur côté serveur lors de la sauvegarde de la tâche.');
        }
    } catch (error) {
        console.error('Erreur réseau :', error);
    }
}


// =========================================================================
// 5. SAUVEGARDE DES FORMULAIRES VIA FETCH (Matières, Examens, Paramètres)
// =========================================================================

// Simulation d'ajout de matière (à remplacer par la vraie requête)
function saveSubject() {
    const name = document.getElementById('m-name').value;
    const coeff = document.getElementById('m-coeff').value;
    
    // TODO Membre 3 : Remplacer ceci par fetch('/student/subjects/new', ...)
    alert(`Enregistrement de la matière : ${name} (Coeff: ${coeff})\nNécessite le backend Symfony.`);
    closeModal();
}

// Simulation d'ajout d'examen
function saveExam() {
    const date = document.getElementById('e-date').value;
    const room = document.getElementById('e-room').value;
    
    alert(`Enregistrement de l'examen le ${date} en salle ${room}\nNécessite le backend Symfony.`);
    closeModal('exam-modal');
}

// Sauvegarde globale des paramètres étudiants (Notifications)
async function saveAllSettings() {
    // Récupération de l'état des cases à cocher (Toggles)
    const examNotif = document.getElementById('exam-notif')?.checked || false;
    const taskNotif = document.getElementById('task-notif')?.checked || false;
    const summaryNotif = document.getElementById('summary-notif')?.checked || false;

    const payload = {
        notifications: {
            examReminder: examNotif,
            taskAlert: taskNotif,
            weeklySummary: summaryNotif
        }
    };

    try {
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const token = csrfMeta ? csrfMeta.content : '';

        // TODO Membre 3 : Adapter l'URL
        const response = await fetch('/student/settings/save', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': token
            },
            body: JSON.stringify(payload)
        });

        if (response.ok) {
            // Affiche un badge de succès ou recharge la page pour voir le FlashMessage Symfony
            window.location.reload(); 
        } else {
            alert('Erreur lors de la sauvegarde de vos préférences.');
        }
    } catch (error) {
        console.error('Erreur:', error);
        alert('Impossible de joindre le serveur.');
    }
}
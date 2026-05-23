/**
 * Fichier : front.js
 * Rôle : Gérer l'interactivité de l'espace Étudiant (Front-office)
 * (Tableau de bord étudiant, gestion des tâches, animations, etc.)
 */

document.addEventListener('DOMContentLoaded', () => {
    console.log("Espace Étudiant chargé avec succès !");

    // =====================================================================
    // 1. ANIMATIONS D'APPARITION (Comme pour l'admin)
    // Utile pour faire apparaître les cartes de cours et de tâches en douceur
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
    // 2. GESTION DES TÂCHES (Exemple pour marquer une tâche comme terminée)
    // À utiliser quand vous aurez créé la liste des devoirs
    // =====================================================================
    const taskCheckboxes = document.querySelectorAll('.task-checkbox');
    
    taskCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', async function() {
            const taskId = this.dataset.id; // Ex: <input type="checkbox" data-id="12">
            const isCompleted = this.checked;
            
            // Ajoute un effet visuel (barrer le texte)
            const taskItem = this.closest('.task-item');
            if (isCompleted) {
                taskItem.classList.add('completed');
            } else {
                taskItem.classList.remove('completed');
            }

            // TODO : Appel AJAX vers Symfony pour mettre à jour la base de données
            /*
            try {
                const response = await fetch(`/student/task/${taskId}/toggle`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ status: isCompleted ? 'terminé' : 'à faire' })
                });
                // Gérer la réponse...
            } catch (error) {
                console.error("Erreur lors de la mise à jour de la tâche", error);
            }
            */
        });
    });

});
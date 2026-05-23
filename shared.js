/**
 * Fichier : shared.js (ou intégré directement dans settings.html.twig)
 * Rôle : Gérer les requêtes asynchrones (AJAX/Fetch) pour la sauvegarde
 * des paramètres administrateur et du mot de passe.
 */

// =====================================================================
// FONCTION : changePassword()
// Rôle : Valider et envoyer la demande de changement de mot de passe
// =====================================================================
async function changePassword() {
    // 1. Récupération des valeurs saisies dans le formulaire
    const currentPw = document.getElementById('current-pw').value;
    const newPw = document.getElementById('new-pw').value;
    const confirmPw = document.getElementById('confirm-pw').value;

    // 2. Validation côté Front-end (Navigateur)
    if (!currentPw || !newPw || !confirmPw) {
        alert('Veuillez remplir tous les champs de mot de passe.');
        return; // Arrête l'exécution si un champ est vide
    }
    if (newPw !== confirmPw) {
        alert('Le nouveau mot de passe et la confirmation ne correspondent pas.');
        return;
    }
    if (newPw.length < 8) {
        alert('Le nouveau mot de passe doit contenir au moins 8 caractères.');
        return;
    }

    // 3. Envoi de la requête au serveur Symfony
    try {
        // Remplace l'URL par la route Symfony exacte de ton contrôleur
        const response = await fetch('/admin/settings/change-password', {
            method: 'POST', // Méthode POST pour l'envoi de données sensibles
            headers: {
                'Content-Type': 'application/json',
                // IMPORTANT : Ajouter le token CSRF pour la sécurité dans Symfony
                // 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content 
            },
            // Conversion de l'objet JS en chaîne JSON
            body: JSON.stringify({
                currentPassword: currentPw,
                newPassword: newPw
            })
        });

        const data = await response.json(); // Analyse de la réponse du serveur

        // 4. Traitement de la réponse
        if (response.ok) {
            alert('Mot de passe mis à jour avec succès !');
            // Vide les champs après succès
            document.getElementById('current-pw').value = '';
            document.getElementById('new-pw').value = '';
            document.getElementById('confirm-pw').value = '';
        } else {
            // Affiche l'erreur renvoyée par le serveur (ex: "Mot de passe actuel incorrect")
            alert(data.message || 'Erreur lors de la mise à jour du mot de passe.');
        }
    } catch (error) {
        console.error('Erreur réseau ou serveur:', error);
        alert('Une erreur réseau est survenue. Veuillez réessayer.');
    }
}

// =====================================================================
// FONCTION : saveAllSettings()
// Rôle : Collecter toutes les données des onglets et les sauvegarder
// =====================================================================
async function saveAllSettings() {
    // 1. Collecte des Informations de Profil
    const prenom = document.getElementById('prenom').value;
    const nom = document.getElementById('nom').value;
    const email = document.getElementById('email').value;

    // 2. Collecte des Paramètres du Système (Cases à cocher et champs texte)
    const maintenanceMode = document.getElementById('maintenance-mode').checked;
    const registrationOpen = document.getElementById('registration-open').checked;
    const emailNotif = document.getElementById('email-notif').checked;
    const maxAttempts = document.getElementById('max-attempts').value;
    const sessionTimeout = document.getElementById('session-timeout').value;

    // 3. Collecte des Préférences de Notification
    const newUserAlert = document.getElementById('new-user-alert').checked;
    const failedLoginAlert = document.getElementById('failed-login-alert').checked;
    const weeklyReport = document.getElementById('weekly-report').checked;

    // 4. Construction de l'objet "Payload" (données à envoyer au serveur)
    const payload = {
        profile: { prenom, nom, email },
        system: {
            maintenanceMode,
            registrationOpen,
            emailNotifications: emailNotif,
            maxLoginAttempts: parseInt(maxAttempts), // Convertit le texte en nombre entier
            sessionTimeout: parseInt(sessionTimeout)
        },
        notifications: {
            newUserAlert,
            failedLoginAlert,
            weeklyReport
        }
    };

    // 5. Envoi des données au contrôleur Symfony
    try {
        const response = await fetch('/admin/settings/save', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                // 'X-CSRF-TOKEN': 'Ajouter ton token CSRF ici'
            },
            body: JSON.stringify(payload)
        });

        // 6. Gestion de la réponse
        if (response.ok) {
            // Recharge la page. Symfony affichera un message "Flash" de succès
            // configuré dans le Contrôleur, qui sera rendu par base.html.twig
            window.location.reload(); 
        } else {
            alert('Erreur lors de la sauvegarde des paramètres.');
        }
    } catch (error) {
        console.error('Erreur réseau:', error);
        alert('Impossible de joindre le serveur.');
    }
}
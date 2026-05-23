/**
 * Fichier : app.js
 * Rôle : Gère l'interactivité côté client pour les pages d'authentification
 * (Connexion / login.html.twig et Inscription / register.html.twig).
 */

// =====================================================================
// FONCTION : togglePw(inputId, iconId)
// Rôle : Basculer la visibilité du mot de passe (clair/caché) et
//        changer l'icône de l'œil (FontAwesome) correspondante.
// Appelé par : onclick="togglePw('id_du_champ', 'id_de_l_icone')" dans Twig.
// =====================================================================
function togglePw(inputId, iconId) {
    // 1. Récupération des éléments du DOM (champ input et icône)
    const inputField = document.getElementById(inputId);
    const iconElement = document.getElementById(iconId);

    // Vérification de sécurité : on arrête la fonction si les éléments n'existent pas
    if (!inputField || !iconElement) return;

    // 2. Logique de bascule (Toggle)
    if (inputField.type === 'password') {
        // Si c'est un mot de passe (caché par des points), on le passe en texte clair
        inputField.type = 'text';
        // On remplace l'icône "œil" par l'icône "œil barré"
        iconElement.classList.remove('fa-eye');
        iconElement.classList.add('fa-eye-slash');
    } else {
        // Si le texte est déjà clair, on le recache
        inputField.type = 'password';
        // On remet l'icône "œil" normale
        iconElement.classList.remove('fa-eye-slash');
        iconElement.classList.add('fa-eye');
    }
}

// =====================================================================
// INITIALISATION : Validation du formulaire d'inscription
// Rôle : Vérifier que les deux mots de passe sont identiques avant
//        d'envoyer la requête à Symfony.
// =====================================================================
document.addEventListener('DOMContentLoaded', () => {
    
    // On cible le formulaire de la page courante
    // S'il y a plusieurs formulaires, on peut être plus précis en ajoutant un ID au <form>
    const authForm = document.querySelector('form'); 

    // On vérifie qu'on est bien sur la page d'inscription et que le formulaire existe
    if (authForm && window.location.pathname.includes('register')) {
        
        authForm.addEventListener('submit', function(e) {
            // Récupération des champs mot de passe et confirmation
            // (Adaptation aux attributs 'name' classiques de Symfony ou aux IDs de ton Twig)
            const passwordInput = document.getElementById('password') || document.querySelector('input[name="password"]');
            const confirmInput = document.getElementById('confirm') || document.querySelector('input[name="confirm"]');

            // Si les deux champs existent sur la page
            if (passwordInput && confirmInput) {
                
                // On vérifie s'ils sont différents
                if (passwordInput.value !== confirmInput.value) {
                    
                    e.preventDefault(); // Bloque la soumission du formulaire au serveur
                    
                    // Affiche une alerte (tu pourrais aussi insérer un message d'erreur en rouge dans le DOM)
                    alert('Les mots de passe ne correspondent pas. Veuillez vérifier votre saisie.');
                    
                    // Optionnel : on vide le champ de confirmation pour forcer la resaisie
                    confirmInput.value = '';
                    confirmInput.focus();
                }
            }
        });
    }
});
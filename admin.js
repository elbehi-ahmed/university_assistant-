/**
 * Fichier : admin.js
 * Rôle : Gère l'interactivité globale de l'interface d'administration
 * (Animations au chargement, recherche d'utilisateurs/emails, filtres, etc.)
 */
document.addEventListener('DOMContentLoaded', () => {
    
    // =====================================================================
    // 1. INITIALISATION DES ANIMATIONS EN CASCADE (STAGGERED ANIMATIONS)
    // Utilisé dans le Dashboard pour faire apparaître les cartes KPI en douceur.
    // =====================================================================
    
    // Sélectionne tous les éléments HTML dont la classe contient "anim-" (ex: anim-1, anim-2)
    const animatedElements = document.querySelectorAll('[class*="anim-"]');
    
    animatedElements.forEach(el => {
        // Extrait le numéro de l'animation à partir du nom de la classe
        const match = el.className.match(/anim-(\d+)/);
        if (match) {
            // Calcule un délai basé sur le numéro (ex: anim-1 = 100ms, anim-2 = 200ms)
            const delay = parseInt(match[1]) * 100; 
            // Applique le délai au style CSS de l'élément
            el.style.animationDelay = `${delay}ms`;
            // Ajoute la classe 'fade-in-up' qui déclenche l'animation CSS réelle
            el.classList.add('fade-in-up'); 
        }
    });

    // =====================================================================
    // 2. GESTIONNAIRE DES BARRES DE RECHERCHE (Utilisateurs & Liste Blanche)
    // Permet de filtrer les tableaux en appuyant sur "Entrée"
    // =====================================================================
    
    // Récupère tous les champs de texte de recherche
    const searchInputs = document.querySelectorAll('.search-input');
    
    searchInputs.forEach(input => {
        // Écoute l'événement "frappe au clavier"
        input.addEventListener('keypress', function (e) {
            // Si la touche pressée est "Entrée" (Enter)
            if (e.key === 'Enter') {
                e.preventDefault(); // Empêche le comportement par défaut du navigateur
                
                const query = this.value.trim(); // Récupère le texte saisi sans les espaces inutiles
                const url = new URL(window.location.href); // Récupère l'URL actuelle
                
                // Met à jour ou supprime le paramètre "q" (requête) dans l'URL
                if (query) {
                    url.searchParams.set('q', query);
                } else {
                    url.searchParams.delete('q');
                }
                
                // Supprime le paramètre 'page' pour revenir à la première page des résultats
                url.searchParams.delete('page'); 
                
                // Recharge la page avec la nouvelle URL (Symfony s'occupera du reste)
                window.location.href = url.toString();
            }
        });
    });

    // =====================================================================
    // 3. GESTIONNAIRE DES FILTRES PAR RÔLE (Page Utilisateurs)
    // Permet de filtrer la liste des utilisateurs par statut ou rôle
    // =====================================================================
    
    const filterSelects = document.querySelectorAll('.filter-select');
    
    filterSelects.forEach(select => {
        // Écoute l'événement "changement de valeur" sur la liste déroulante
        select.addEventListener('change', function () {
            const role = this.value; // Récupère le rôle sélectionné
            const url = new URL(window.location.href);
            
            // Met à jour l'URL avec le paramètre "role"
            if (role) {
                url.searchParams.set('role', role);
            } else {
                url.searchParams.delete('role');
            }
            
            // Réinitialise la pagination à la page 1
            url.searchParams.delete('page');
            
            // Recharge la page
            window.location.href = url.toString();
        });
    });

    // =====================================================================
    // 4. DÉMO DE L'UPLOAD D'AVATAR (Page Paramètres / Profil)
    // Simule le comportement d'un bouton d'upload d'image
    // =====================================================================
    
    const avatarUploadBtn = document.querySelector('.avatar-upload button');
    if (avatarUploadBtn) {
        avatarUploadBtn.addEventListener('click', (e) => {
            e.preventDefault();
            
            // Crée un champ input de type "file" invisible
            const fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.accept = 'image/png, image/jpeg'; // N'accepte que les images
            
            // Que faire lorsque l'utilisateur sélectionne un fichier :
            fileInput.onchange = (e) => {
                const file = e.target.files[0];
                if (file) {
                    // TODO pour l'équipe (Membre 4) : Implémenter l'upload en AJAX vers Symfony ici
                    alert(`Fichier sélectionné : ${file.name}. (L'upload AJAX doit être configuré)`);
                }
            };
            
            // Simule un clic sur ce champ invisible pour ouvrir la fenêtre de choix de fichier
            fileInput.click();
        });
    }
});
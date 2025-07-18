/*
====================================================================================
    Fichier : navbar.js

    Rôle :
    Ce fichier gère dynamiquement l’affichage de la barre de navigation principale (#mainNav)
    en fonction de l’état de connexion de l’utilisateur. Il adapte les liens présents dans la
    barre de navigation selon que l’utilisateur est connecté ou non.

    Fonctionnement :
    - Au chargement du DOM, interroge le backend (session-check.php) pour déterminer si l’utilisateur est connecté.
    - Met à jour dynamiquement le contenu HTML de la barre de navigation selon la réponse :
        - Si l’utilisateur est connecté : liens HOME, EVENTS, DASHBOARD, Classement, Déconnexion.
        - Si l’utilisateur n’est pas connecté : liens HOME, EVENTS, Classement, Connexion (modal).
    - Prend en charge l’ouverture de la modale de connexion et du classement Top 10 via des fonctions JS externes.

    Interactions avec le reste du projet :
    - Utilise session-check.php (backend) pour déterminer l’état de connexion.
    - Nécessite la présence d’un élément avec l’id "mainNav" dans la page.
    - S’utilise sur toutes les pages du site pour assurer une navigation cohérente.

====================================================================================
*/

// Met à jour dynamiquement la barre de navigation selon l'état de connexion
async function updateNavbar() {
  const nav = document.getElementById("mainNav");
  if (!nav) return;

  try {
    // Appel AJAX pour vérifier l'état de connexion de l'utilisateur
    const response = await fetch("/esportify/back/includes/session-check.php");
    const data = await response.json();

    // Si utilisateur connecté, affiche la navbar avec accès dashboard et déconnexion
    if (data.loggedIn) {
      nav.innerHTML = `
        <a href="/esportify/front/index.html">HOME</a>
        <a href="/esportify/front/events.html">EVENTS</a>
        <a href="/esportify/front/dashboard.html">DASHBOARD</a>
        <a href="#" onclick="openTop10Modal(); return false;" title="Classement"><span class="icon-top">♛</span></a>
        <a href="/esportify/back/controllers/logout.php">DÉCONNEXION</a>
      `;
    } 
    // Si utilisateur non connecté, affiche la navbar sans dashboard ni déconnexion
    else {
      nav.innerHTML = `
        <a href="/esportify/front/index.html">HOME</a>
        <a href="/esportify/front/events.html">EVENTS</a>
        <a href="#" onclick="openTop10Modal(); return false;" title="Classement"><span class="icon-top">♛</span></a>
        <a href="#" onclick="openModal(); return false;">CONNEXION</a>
      `;
    }
  } catch (err) {
    // Gestion des erreurs de chargement ou de réponse du backend
    console.error("Erreur navbar :", err);
  }
}

// Initialise la navbar dynamiquement au chargement de la page
document.addEventListener("DOMContentLoaded", updateNavbar);

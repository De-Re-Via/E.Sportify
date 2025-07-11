// navbar.js
// ➤ Rôle : afficher dynamiquement la barre de navigation selon l'état de connexion

async function updateNavbar() {
  const nav = document.getElementById("mainNav");
  if (!nav) return;

  try {
    const response = await fetch("/esportify/back/includes/session-check.php");
    const data = await response.json();

    // Utilisateur connecté
    if (data.loggedIn) {
      nav.innerHTML = `
        <a href="/esportify/front/index.html">HOME</a>
        <a href="/esportify/front/events.html">EVENTS</a>
        <a href="/esportify/front/dashboard.html">DASHBOARD</a>
        <a href="#" onclick="openTop10Modal(); return false;" title="Classement"><span class="icon-top">♛</span></a>
        <a href="/esportify/back/controllers/logout.php">DÉCONNEXION</a>
      `;
    } 
    // Utilisateur non connecté
    else {
      nav.innerHTML = `
        <a href="/esportify/front/index.html">HOME</a>
        <a href="/esportify/front/events.html">EVENTS</a>
        <a href="#" onclick="openTop10Modal(); return false;" title="Classement"><span class="icon-top">♛</span></a>
        <a href="#" onclick="openModal(); return false;">CONNEXION</a>
      `;
    }
  } catch (err) {
    console.error("Erreur navbar :", err);
  }
}

document.addEventListener("DOMContentLoaded", updateNavbar);

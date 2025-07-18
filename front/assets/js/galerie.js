/*
====================================================================================
    Fichier : galerie.js

    Rôle :
    Ce fichier gère l’affichage dynamique du carrousel d’images de la galerie.
    Il permet de naviguer entre les images, d’afficher des miniatures cliquables,
    de gérer le défilement automatique, et propose une interface d’administration
    (ajout/suppression d’images) si l’utilisateur est administrateur.

    Fonctionnement :
    - Charge dynamiquement les images de la galerie depuis la base de données via AJAX.
    - Gère la navigation manuelle (précédent/suivant) et automatique (auto-slide).
    - Affiche une rangée de miniatures sous la grande image.
    - Ajoute une interface d’administration si l’utilisateur est admin (vérifié côté serveur).
    - Permet à l’admin d’ajouter ou de supprimer des images via une modale dédiée.

    Interactions avec le reste du projet :
    - Utilise galerie.php côté serveur pour charger, ajouter et supprimer des images.
    - Nécessite une structure HTML comprenant les éléments requis (carouselImage, prev, next, carousel-thumbs, etc.).
    - L’interface admin est injectée dynamiquement dans la page si l’utilisateur est reconnu comme administrateur.

====================================================================================
*/

// Exécution au chargement du DOM
document.addEventListener("DOMContentLoaded", () => {
  let images = [];
  let idx = 0;
  let autoSlide = null;

  const img = document.getElementById("carouselImage");
  const prevBtn = document.getElementById("prev");
  const nextBtn = document.getElementById("next");
  const thumbsDiv = document.getElementById("carousel-thumbs");

  // Gestion des boutons de navigation
  prevBtn.onclick = () => show(idx - 1, true);
  nextBtn.onclick = () => show(idx + 1, true);

  // Chargement des images de la galerie depuis la BDD (via AJAX)
  function loadImages() {
    fetch("../back/controllers/galerie.php?action=fetch", {credentials:"same-origin"})
      .then(r => r.json())
      .then(data => {
        images = data.images;
        show(0, true);     // Affiche la première image
        makeThumbs();      // Génère les miniatures sous le carrousel
      });
  }
  loadImages();

  // Défilement automatique du carrousel (toutes les 4,2 secondes)
  function startAuto() {
    if(autoSlide) clearInterval(autoSlide);
    autoSlide = setInterval(()=>show(idx+1), 4200);
  }
  function stopAuto() { if(autoSlide) clearInterval(autoSlide); }

  // Affiche l’image d’indice i, met à jour les miniatures, relance l’auto-slide
  function show(i, stop) {
    if (!images.length) return;
    idx = (i + images.length) % images.length;
    img.src = images[idx];
    updateThumbs();
    if (stop) { stopAuto(); startAuto(); }
  }

  // Génère dynamiquement les miniatures sous la galerie
  function makeThumbs() {
    thumbsDiv.innerHTML = '';
    images.forEach((url, i) => {
      const th = document.createElement('img');
      th.src = url;
      th.className = "carousel-thumb" + (i===idx ? " selected":"");
      th.onclick = ()=>show(i,true);
      thumbsDiv.appendChild(th);
    });
  }

  // Met à jour l’état "sélectionné" sur les miniatures
  function updateThumbs() {
    Array.from(thumbsDiv.children).forEach((th, i) => {
      if (i === idx) th.classList.add("selected");
      else th.classList.remove("selected");
    });
  }

  // Relance l’auto-slide après action manuelle
  img.onload = startAuto;

  // Vérifie si l’utilisateur est admin et injecte l’UI admin si oui
  fetch("../back/controllers/admin_actions.php?action=check_admin", {credentials:"same-origin"})
    .then(r => r.json())
    .then(({isAdmin}) => { if (isAdmin) injectAdminUI(); });

  // Interface admin pour ajouter/supprimer des images
  function injectAdminUI() {
    // Bouton paramètre d’accès à la gestion
    const btn = document.createElement("button");
    btn.className = "admin-icon";
    btn.textContent = "⚙️";
    btn.title = "Gérer la galerie";
    btn.onclick = showModal;
    document.querySelector('.galerie').appendChild(btn);

    // Création de la modale d’administration
    let modal = document.createElement("div");
    modal.id = "adminGalerieModal";
    modal.className = "modal";
    modal.style.display = "none";
    modal.innerHTML = `
      <div class="modal-content">
        <button class="close-modal-btn" id="closeGalerieModal" title="Fermer">&times;</button>
        <h3 style="margin-bottom:1em;">Gestion de la galerie</h3>
        <form id="uploadForm" enctype="multipart/form-data" style="margin-bottom:1em;">
          <input type="file" name="image" accept="image/*" required>
          <button>Ajouter</button>
        </form>
        <div id="galerieAdminGrid"></div>
      </div>`;
    document.body.appendChild(modal);

    // Gestion de la fermeture de la modale admin
    document.getElementById("closeGalerieModal").onclick = () => modal.style.display = "none";
    // Soumission du formulaire d’ajout d’image (AJAX)
    document.getElementById("uploadForm").onsubmit = e => {
      e.preventDefault();
      const fd = new FormData(e.target);
      fetch("../back/controllers/galerie.php?action=upload", {method:"POST",body:fd,credentials:"same-origin"})
        .then(()=>{ fetchAdminGrid(); loadImages(); });
    };
    fetchAdminGrid();
  }

  // Affiche la modale admin et recharge la grille d’administration
  function showModal() {
    document.getElementById("adminGalerieModal").style.display = "flex";
    fetchAdminGrid();
  }

  // Récupère et affiche la grille d’images côté admin (avec boutons de suppression)
  function fetchAdminGrid() {
    fetch("../back/controllers/galerie.php?action=fetch_admin", {credentials:"same-origin"})
      .then(r => r.json())
      .then(data => {
        let html = data.images.map(img =>
          `<div style="position:relative;">
             <img src="${img.url}">
             <button onclick="deleteImage(${img.id})" class="delete-btn" title="Supprimer">&times;</button>
           </div>`
        ).join('');
        document.getElementById("galerieAdminGrid").innerHTML = html;
      });
  }

  // Suppression d’une image depuis l’UI admin (action globale)
  window.deleteImage = function(id) {
    if (!confirm("Supprimer cette image ?")) return;
    fetch("../back/controllers/galerie.php?action=delete&id=" + id, {credentials:"same-origin"})
      .then(()=>{ fetchAdminGrid(); loadImages(); });
  }
});

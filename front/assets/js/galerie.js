document.addEventListener("DOMContentLoaded", () => {
  let images = [];
  let idx = 0;
  let autoSlide = null;

  const img = document.getElementById("carouselImage");
  const prevBtn = document.getElementById("prev");
  const nextBtn = document.getElementById("next");
  const thumbsDiv = document.getElementById("carousel-thumbs");

  // Boutons de navigation (simples)
  prevBtn.onclick = () => show(idx - 1, true);
  nextBtn.onclick = () => show(idx + 1, true);

  // Chargement images galerie depuis la BDD
  function loadImages() {
    fetch("../back/controllers/galerie.php?action=fetch", {credentials:"same-origin"})
      .then(r => r.json())
      .then(data => {
        images = data.images;
        show(0, true);
        makeThumbs();
      });
  }
  loadImages();

  // Défilement automatique
  function startAuto() {
    if(autoSlide) clearInterval(autoSlide);
    autoSlide = setInterval(()=>show(idx+1), 4200);
  }
  function stopAuto() { if(autoSlide) clearInterval(autoSlide); }

  function show(i, stop) {
    if (!images.length) return;
    idx = (i + images.length) % images.length;
    img.src = images[idx];
    updateThumbs();
    if (stop) { stopAuto(); startAuto(); }
  }

  // Miniatures sous la galerie
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

  function updateThumbs() {
    Array.from(thumbsDiv.children).forEach((th, i) => {
      if (i === idx) th.classList.add("selected");
      else th.classList.remove("selected");
    });
  }

  // Relance l’auto-slide après action manuelle
  img.onload = startAuto;

  // ADMIN : Vérifie admin, injecte bouton/modale si admin
  fetch("../back/controllers/admin_actions.php?action=check_admin", {credentials:"same-origin"})
    .then(r => r.json())
    .then(({isAdmin}) => { if (isAdmin) injectAdminUI(); });

  function injectAdminUI() {
    // Bouton paramètre (⚙️)
    const btn = document.createElement("button");
    btn.className = "admin-icon";
    btn.textContent = "⚙️";
    btn.title = "Gérer la galerie";
    btn.onclick = showModal;
    document.querySelector('.galerie').appendChild(btn);

    // Modal admin
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

    document.getElementById("closeGalerieModal").onclick = () => modal.style.display = "none";
    document.getElementById("uploadForm").onsubmit = e => {
      e.preventDefault();
      const fd = new FormData(e.target);
      fetch("../back/controllers/galerie.php?action=upload", {method:"POST",body:fd,credentials:"same-origin"})
        .then(()=>{ fetchAdminGrid(); loadImages(); });
    };
    fetchAdminGrid();
  }

  function showModal() {
    document.getElementById("adminGalerieModal").style.display = "flex";
    fetchAdminGrid();
  }

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

  window.deleteImage = function(id) {
    if (!confirm("Supprimer cette image ?")) return;
    fetch("../back/controllers/galerie.php?action=delete&id=" + id, {credentials:"same-origin"})
      .then(()=>{ fetchAdminGrid(); loadImages(); });
  }
});

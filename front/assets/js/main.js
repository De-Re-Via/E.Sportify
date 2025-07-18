/*
====================================================================================
    Fichier : main.js

    Rôle :
    Ce fichier gère les interactions globales du site Esportify. Il centralise :
    - La gestion du menu burger mobile.
    - L’ouverture/fermeture de la modale d’authentification.
    - Le passage entre les onglets Connexion et Inscription.
    - La vérification des mots de passe à l’inscription.
    - L’envoi AJAX du formulaire d’inscription.
    - L’ouverture et la fermeture du modal "Top 10".
    - L’initialisation et la gestion du système de chat global.

    Fonctionnement :
    - Détecte le chargement du DOM pour initialiser les systèmes nécessaires.
    - Gère dynamiquement les modales, les onglets et la validation des formulaires.
    - Gère l’envoi AJAX du formulaire d’inscription avec gestion d’erreur et d’alertes utilisateur.
    - Met à jour le classement Top 10 dans une modale dédiée.
    - Gère le chat global en AJAX (affichage, envoi, suppression messages, notifications, etc.).

    Interactions avec le reste du projet :
    - Utilise register.php et login.php pour l’authentification/inscription.
    - Utilise top10.php pour le classement.
    - Utilise chat.php pour la discussion globale.
    - Nécessite la présence des éléments HTML correspondants (nav, modales, formulaires...).

====================================================================================
*/

// Initialisation principale à la fin du chargement du DOM
document.addEventListener("DOMContentLoaded", () => {
  console.log("E.SPORTIFY chargé !");
  initChatSystem(); // Initialise la discussion à chargement
});

// MENU BURGER : ouverture/fermeture sur clic
function toggleMenu() {
  const nav = document.querySelector('.main-nav');
  nav.classList.toggle('show');
}

// Ouvre la modale d’authentification
function openModal() {
  document.getElementById("authModal").style.display = "block";
}

// Ferme la modale d’authentification
function closeModal() {
  document.getElementById("authModal").style.display = "none";
}

// Gestion des onglets Connexion/Inscription dans la modale
function showTab(tab) {
  const loginForm = document.getElementById("loginForm");
  const registerForm = document.getElementById("registerForm");
  const loginTab = document.getElementById("loginTab");
  const registerTab = document.getElementById("registerTab");

  if (tab === "login") {
    loginForm.classList.remove("hidden");
    registerForm.classList.add("hidden");
    loginTab.classList.add("active");
    registerTab.classList.remove("active");
  } else {
    loginForm.classList.add("hidden");
    registerForm.classList.remove("hidden");
    loginTab.classList.remove("active");
    registerTab.classList.add("active");
  }
}

// Vérifie si les deux mots de passe sont identiques à l’inscription
function checkPasswordsMatch() {
  const password = document.getElementById("password").value;
  const confirm = document.getElementById("confirm_password").value;
  const error = document.getElementById("passwordError");

  if (password !== confirm) {
    error.style.display = "block";
    return false;
  }

  error.style.display = "none";
  return true;
}

// Gère l’envoi AJAX du formulaire d’inscription avec vérification de la correspondance des mots de passe
document.addEventListener("DOMContentLoaded", () => {
  const registerForm = document.getElementById("registerForm");

  if (registerForm) {
    registerForm.addEventListener("submit", async function (e) {
      e.preventDefault();
      if (!checkPasswordsMatch()) return;

      const formData = new FormData(registerForm);

      try {
        const response = await fetch("/esportify/back/controllers/register.php", {
          method: "POST",
          body: formData
        });

        const text = await response.text();

        if (text.includes("réussie")) {
          alert("Inscription réussie !");
          registerForm.reset();
        } else {
          alert(text);
        }
      } catch (err) {
        console.error("Erreur serveur :", err);
        alert("Erreur lors de l’inscription.");
      }
    });
  }
});

// Ouvre la modale Top 10 et charge le classement en AJAX
function openTop10Modal() {
  const modal = document.getElementById("top10Modal");
  const content = document.getElementById("top10Content");

  if (!modal || !content) return;

  modal.style.display = "flex";
  content.innerHTML = "Chargement...";

  fetch("/esportify/back/pages/top10.php")
    .then(res => res.text())
    .then(html => {
      content.innerHTML = html;
    })
    .catch(err => {
      content.innerHTML = "<p>Erreur de chargement du classement.</p>";
      console.error("Erreur top10 :", err);
    });
}

// Ferme la modale Top 10
function closeTop10Modal() {
  const modal = document.getElementById("top10Modal");
  if (modal) modal.style.display = "none";
}

// Gestion du chat global
let chatInterval = null; // Pour éviter les doublons de setInterval
let lastMessageId = null;

function initChatSystem() {
  const form = document.getElementById("chat-form");
  const box = document.getElementById("chat-box");
  const audio = document.getElementById("notifSound");
  const icon = document.querySelector(".icon-discussion");

  if (!form || !box) return;

  let isAdmin = false;

  // Fonction de chargement et d’affichage des messages
  function loadMessages() {
    fetch("/esportify/back/controllers/chat.php")
      .then(res => res.json())
      .then(data => {
        isAdmin = data.isAdmin === true;
        const messages = data.messages;

        // Détection d’un nouveau message pour notification
        if (messages.length > 0 && messages[messages.length - 1].id !== lastMessageId) {
          const lastMsg = messages[messages.length - 1];
          if (lastMessageId !== null) {
            // Son et vibration uniquement si ce n'est pas le 1er chargement ET si ce n'est pas moi
            if (audio && lastMsg.auteur !== window.currentUsername) {
              audio.currentTime = 0;
              audio.play().catch(() => {});
            }
            if (icon) {
              icon.classList.add("vibrate");
              setTimeout(() => icon.classList.remove("vibrate"), 300);
            }
          }
          lastMessageId = lastMsg.id;
        }

        // Affichage des messages dans le chat
        box.innerHTML = messages.map(msg => `
          <div class="chat-message">
            <strong>${msg.auteur}</strong> : ${msg.contenu}<br>
            <small>${msg.date_envoi}</small>
            ${isAdmin ? `<button onclick="deleteMessage(${msg.id})" style="float:right;">🗑️</button>` : ""}
          </div>
        `).join('');
      });
  }

  // Suppression d’un message (admin uniquement)
  function deleteMessage(id) {
    if (!isAdmin) return;
    if (!confirm("Supprimer ce message ?")) return;

    fetch("/esportify/back/controllers/chat.php", {
      method: "DELETE",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id })
    })
      .then(res => res.json())
      .then(data => {
        if(data.success) {
          loadMessages();
        } else if(data.error) {
          alert(data.error);
        }
      });
  }

  // Gestion de la soumission du formulaire de chat
  if (!form.dataset.listenerAttached) {
    form.addEventListener("submit", (e) => {
      e.preventDefault();
      const contenu = document.getElementById("contenu").value.trim().slice(0, 100);
      if (!contenu) return alert("Message vide !");

      fetch("/esportify/back/controllers/chat.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ contenu })
      })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            document.getElementById("contenu").value = "";
            loadMessages();
          } else if (data.error) {
            alert(data.error);
          }
        });
    });

    form.dataset.listenerAttached = true;
  }

  // Chargement initial et rafraîchissement automatique des messages
  loadMessages();
  if (!chatInterval) {
    chatInterval = setInterval(loadMessages, 10000);
  }

  window.deleteMessage = deleteMessage;
}

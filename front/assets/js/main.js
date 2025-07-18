// main.js
// ➤ Rôle : interactions globales (menu, modale connexion, vérification inscription, top10 modal)

document.addEventListener("DOMContentLoaded", () => {
  console.log("E.SPORTIFY chargé !");
  initChatSystem(); // Initialise la discussion à chargement
});

// ➤ MENU BURGER : toggle sur clic
function toggleMenu() {
  const nav = document.querySelector('.main-nav');
  nav.classList.toggle('show');
}

// ➤ Ouvre la modale de connexion
function openModal() {
  document.getElementById("authModal").style.display = "block";
}

// ➤ Ferme la modale de connexion
function closeModal() {
  document.getElementById("authModal").style.display = "none";
}

// ➤ Gestion des onglets Connexion/Inscription
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

// ➤ Vérifie les mots de passe à l'inscription
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

// ➤ Gère l’envoi du formulaire d’inscription
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

// ➤ Ouvre le modal Top 10 (♛)
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

// ➤ Ferme le modal Top 10
function closeTop10Modal() {
  const modal = document.getElementById("top10Modal");
  if (modal) modal.style.display = "none";
}

// ➤ DISCUSSION / CHAT GLOBAL (sans champ pseudo)
let chatInterval = null; // pour éviter les doublons d'intervalle
let lastMessageId = null;

function initChatSystem() {
  const form = document.getElementById("chat-form");
  const box = document.getElementById("chat-box");
  const audio = document.getElementById("notifSound");
  const icon = document.querySelector(".icon-discussion");

  if (!form || !box) return;

  let isAdmin = false;

  function loadMessages() {
    fetch("/esportify/back/controllers/chat.php")
      .then(res => res.json())
      .then(data => {
        isAdmin = data.isAdmin === true;
        const messages = data.messages;

        // Vérifie si un nouveau message est arrivé
        if (messages.length > 0 && messages[0].id !== lastMessageId) {
          if (lastMessageId !== null) {
            // Ne joue le son et la vibration que si ce n'est pas le 1er chargement
            if (audio) audio.play();
            if (icon) {
              icon.classList.add("vibrate");
              setTimeout(() => icon.classList.remove("vibrate"), 300);
            }
          }
          lastMessageId = messages[0].id;
        }

        box.innerHTML = messages.map(msg => `
          <div class="chat-message">
            <strong>${msg.auteur}</strong> : ${msg.contenu}<br>
            <small>${msg.date_envoi}</small>
            ${isAdmin ? `<button onclick="deleteMessage(${msg.id})" style="float:right;">🗑️</button>` : ""}
          </div>
        `).join('');
      });
  }

  function deleteMessage(id) {
    if (!isAdmin) return;
    if (!confirm("Supprimer ce message ?")) return;

    fetch("/esportify/back/controllers/chat.php", {
      method: "DELETE",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id })
    })
      .then(res => res.text())
      .then(msg => {
        alert(msg);
        loadMessages();
      });
  }

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
        .then(res => res.text())
        .then(msg => {
          alert(msg);
          document.getElementById("contenu").value = "";
          loadMessages();
        });
    });

    form.dataset.listenerAttached = true;
  }

  // Lancer le chargement initial + protection double appel
  loadMessages();
  if (!chatInterval) {
    chatInterval = setInterval(loadMessages, 10000);
  }

  window.deleteMessage = deleteMessage;
}

// Lancer à chargement DOM
document.addEventListener("DOMContentLoaded", () => {
  initChatSystem();
});

<?php
require_once 'connexionAll.php';
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Accueil – Créer une partie</title>
    <link rel="stylesheet" href="style.css" />
</head>

<body>

    <header>
        <h1>Mon jeu</h1>
        <button class="btn-create" onclick="createGame()">Créer une partie</button>
    </header>

    <main>
        <div class="carousel-container">
            <h2>Vos parties récentes</h2>
            <div class="carousel" id="carousel"></div>
        </div>
    </main>

    <!-- Step 1 Modal (hidden by default) -->
    <div id="step1Modal" class="modal" style="display:none;">
        <div class="modal-content" role="dialog" aria-modal="true" aria-labelledby="step1Title">
            <button class="close-btn" onclick="closeStep1()" aria-label="Fermer">✕</button>
+            <div style="display:flex;align-items:center;gap:12px;margin-bottom:6px;">
+                <div style="width:46px;height:46px;border-radius:10px;background:linear-gradient(180deg,#ede8ff,#efeaff);display:flex;align-items:center;justify-content:center;font-size:20px;color:#7b61ff">🔪</div>
+                <div>
+                    <h2 id="step1Title">Créer une partie</h2>
+                    <div style="color:#666;font-size:13px">Choisissez le type de jeu, un thème et un bref synopsis</div>
+                </div>
+            </div>
+            <form id="step1Form" onsubmit="event.preventDefault(); submitStep1()">
+                <label for="gameTypeSelect">Type de jeu</label>
+                <select id="gameTypeSelect" required>
+                    <option value="">Chargement...</option>
+                </select>
+
+                <label for="themeInput">Thème</label>
+                <input id="themeInput" type="text" maxlength="255" placeholder="Ex: Mariage dans la campagne" />
+
+                <label for="synopsisInput">Synopsis (optionnel)</label>
+                <textarea id="synopsisInput" rows="4" placeholder="Décrivez brièvement le contexte..."></textarea>
+
+                <div class="form-actions">
+                    <button id="step1NextBtn" class="btn-primary" type="submit">Créer la partie</button>
+                    <button type="button" class="btn-muted" onclick="closeStep1()">Annuler</button>
+                </div>
+                <div id="step1Message" class="status" role="status"></div>
+            </form>
        </div>
    </div>

    <footer>
        <a href="contact.html">Contact</a>
    </footer>

    <script>
        const games = [];
        let currentPartyId = null;

        function renderCarousel() {
            const carousel = document.getElementById('carousel');
            carousel.innerHTML = '';

            games.slice(0, 3).forEach(game => {
                const card = document.createElement('div');
                card.className = 'card';
                card.innerHTML = `
          <div class="card-title">${game.name}</div>
          <div class="card-subtitle">${game.date}</div>
        `;
                carousel.appendChild(card);
            });
        }

        async function createGame() {
            openStep1();
            await loadGameTypes();
            // focus the select after it is populated
            setTimeout(() => {
                const sel = document.getElementById('gameTypeSelect');
                if (sel) sel.focus();
            }, 60);
        }

        function openStep1() {
            const modal = document.getElementById('step1Modal');
            modal.style.display = 'flex';
            document.getElementById('step1Message').textContent = '';
            document.addEventListener('keydown', onModalKeyDown);
        }

        function closeStep1() {
            const modal = document.getElementById('step1Modal');
            modal.style.display = 'none';
            document.removeEventListener('keydown', onModalKeyDown);
        }

        function onModalKeyDown(e) {
            if (e.key === 'Escape') closeStep1();
        }

        async function loadGameTypes() {
            const sel = document.getElementById('gameTypeSelect');
            sel.innerHTML = '<option value="">Chargement...</option>';
            try {
                const res = await fetch('/api/game_types.php');
                const data = await res.json();
                sel.innerHTML = '<option value="">-- Choisissez --</option>' + data.map(gt => `<option value="${gt.id}">${gt.name}</option>`).join('');
            } catch (e) {
                sel.innerHTML = '<option value="">Erreur de chargement</option>';
            }
        }

        async function submitStep1() {
            const sel = document.getElementById('gameTypeSelect');
            const theme = document.getElementById('themeInput').value.trim();
            const synopsis = document.getElementById('synopsisInput').value.trim();
            const message = document.getElementById('step1Message');
            const btn = document.getElementById('step1NextBtn');

            const payload = {
                game_type_id: parseInt(sel.value || 0, 10),
                theme,
                synopsis
            };

            if (!payload.game_type_id) {
                message.textContent = 'Veuillez choisir un type de jeu.';
                message.className = 'status error';
                return;
            }

            message.textContent = '';
            message.className = 'status';
            btn.disabled = true;
            btn.textContent = 'Création en cours…';

            try {
                const res = await fetch('/api/party.php?action=create', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    currentPartyId = data.id;
                    message.className = 'status success';
                    message.innerHTML = `Partie initialisée (ID: ${data.id}) <span class="success-badge">✓ Créée</span>`;
                    btn.textContent = 'Créée';
                    setTimeout(() => closeStep1(), 1200);
                } else {
                    message.className = 'status error';
                    message.textContent = 'Erreur: ' + (data.error || 'inconnue');
                    btn.disabled = false;
                    btn.textContent = 'Créer la partie';
                }
            } catch (e) {
                message.className = 'status error';
                message.textContent = 'Erreur réseau';
                btn.disabled = false;
                btn.textContent = 'Créer la partie';
            }
        }

        renderCarousel();
    </script>


</body>

</html>
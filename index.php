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
        <div class="modal-content">
            <h2>Créer une partie — Étape 1</h2>
            <form id="step1Form" onsubmit="event.preventDefault(); submitStep1()">
                <label for="gameTypeSelect">Type de jeu</label>
                <select id="gameTypeSelect" required>
                    <option value="">Chargement...</option>
                </select>

                <label for="themeInput">Thème</label>
                <input id="themeInput" type="text" maxlength="255" />

                <label for="synopsisInput">Synopsis (optionnel)</label>
                <textarea id="synopsisInput" rows="4"></textarea>

                <div class="form-actions">
                    <button id="step1NextBtn" type="submit">Suivant</button>
                    <button type="button" onclick="closeStep1()">Annuler</button>
                </div>
                <div id="step1Message" role="status"></div>
            </form>
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
        }

        function openStep1() {
            document.getElementById('step1Modal').style.display = 'block';
            document.getElementById('step1Message').textContent = '';
        }

        function closeStep1() {
            document.getElementById('step1Modal').style.display = 'none';
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

            const payload = {
                game_type_id: parseInt(sel.value || 0, 10),
                theme,
                synopsis
            };

            if (!payload.game_type_id) {
                message.textContent = 'Veuillez choisir un type de jeu.';
                return;
            }

            message.textContent = 'Initialisation...';
            try {
                const res = await fetch('/api/party.php?action=create', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    currentPartyId = data.id;
                    message.textContent = `Partie initialisée (ID: ${data.id}) - Prêt pour l'étape suivante.`;
                    setTimeout(() => closeStep1(), 1200);
                } else {
                    message.textContent = 'Erreur: ' + (data.error || 'inconnue');
                }
            } catch (e) {
                message.textContent = 'Erreur réseau';
            }
        }

        renderCarousel();
    </script>


</body>

</html>
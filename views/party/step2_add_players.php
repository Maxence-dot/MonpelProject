<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Étape 2 : Ajout des Joueurs - Murder Party Maker</title>
    <?php $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'); ?>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/step2.css">
</head>
<body>
    <div class="step-container">
        <div class="progress-bar">
            <div class="progress-step">Étape 1: Infos</div>
            <div class="progress-step active">Étape 2: Joueurs</div>
            <div class="progress-step">Étape 3: Relations</div>
        </div>

        <h1>Ajout des Joueurs</h1>
        <p>Combien de joueurs participeront à cette Murder Party ? Saisissez leurs prénoms.</p>

        <form id="playersForm">
            <input type="hidden" id="partyId" value="<?= htmlspecialchars($party['id']) ?>">
            
            <div class="form-group">
                <label for="playerCount">Nombre de joueurs :</label>
                <input type="number" id="playerCount" min="2" max="20" value="<?= count($characters) > 0 ? count($characters) : 4 ?>" onchange="generatePlayerFields()">
            </div>

            <div id="playerList" class="player-list"></div>

            <button type="button" class="btn-add-player" onclick="addPlayerField()">+ Ajouter un joueur</button>

            <button type="submit" class="btn-next">Suivant →</button>
        </form>
    </div>

    <script>
        const basePath = '<?= $basePath ?>';
        const existingCharacters = <?= json_encode($characters) ?>;
        let playerCount = parseInt(document.getElementById('playerCount').value);

        function generatePlayerFields() {
            playerCount = parseInt(document.getElementById('playerCount').value);
            const container = document.getElementById('playerList');
            container.innerHTML = '';

            for (let i = 0; i < playerCount; i++) {
                const existingName = existingCharacters[i] ? existingCharacters[i].firstname : '';
                addPlayerFieldToContainer(i, existingName);
            }
        }

        function addPlayerFieldToContainer(index, name = '') {
            const container = document.getElementById('playerList');
            const div = document.createElement('div');
            div.className = 'player-item';
            div.innerHTML = `
                <input type="text" name="player[]" placeholder="Prénom du joueur ${index + 1}" value="${name}" required>
                <button type="button" onclick="removePlayerField(this)">✖</button>
            `;
            container.appendChild(div);
        }

        function addPlayerField() {
            playerCount++;
            document.getElementById('playerCount').value = playerCount;
            addPlayerFieldToContainer(playerCount - 1);
        }

        function removePlayerField(button) {
            if (document.querySelectorAll('.player-item').length > 2) {
                button.parentElement.remove();
                playerCount--;
                document.getElementById('playerCount').value = playerCount;
            } else {
                alert('Vous devez avoir au moins 2 joueurs.');
            }
        }

        // Initialisation
        generatePlayerFields();

        // Soumission du formulaire
        document.getElementById('playersForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const partyId = document.getElementById('partyId').value;
            const playerInputs = document.querySelectorAll('input[name="player[]"]');
            const players = Array.from(playerInputs).map(input => ({
                firstname: input.value.trim()
            })).filter(p => p.firstname !== '');

            if (players.length < 2) {
                alert('Vous devez ajouter au moins 2 joueurs.');
                return;
            }

            try {
                const response = await fetch(basePath + '/api/characters.php?action=save', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        party_id: partyId,
                        players: players
                    })
                });

                const data = await response.json();

                if (data.success) {
                    window.location.href = basePath + `/party/step3?id=${partyId}`;
                } else {
                    alert('Erreur: ' + data.error);
                }
            } catch (error) {
                alert('Erreur lors de la sauvegarde: ' + error.message);
            }
        });
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Étape 3 : Relations et Backgrounds - Murder Party Maker</title>
    <?php $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'); ?>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/modal.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/step3.css">
</head>
<body>
    <div class="step-container">
        <div class="progress-bar">
            <div class="progress-step">Étape 1: Infos</div>
            <div class="progress-step">Étape 2: Joueurs</div>
            <div class="progress-step active">Étape 3: Relations</div>
        </div>

        <h1>Relations et Backgrounds</h1>
        <p>Définissez les backgrounds de vos personnages et créez des relations entre eux.</p>

        <button class="btn-add-character" onclick="addNewCharacter()">+ Ajouter un Personnage</button>

        <div id="charactersGrid" class="characters-grid"></div>

        <button class="btn-finish" onclick="finishParty()">Terminer la Création</button>
    </div>

    <!-- Modale pour ajouter une relation -->
    <div id="relationModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeRelationModal()">&times;</span>
            <h2>Ajouter une Relation</h2>
            <form id="relationForm">
                <input type="hidden" id="currentCharacterId">
                
                <div class="form-group">
                    <label for="targetCharacter">Avec qui ?</label>
                    <select id="targetCharacter" required>
                        <option value="">Choisir un personnage...</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="relationType">Type de relation</label>
                    <input type="text" id="relationType" placeholder="Ex: Amant, Rival, Frère, Complice..." required>
                </div>

                <div class="form-group">
                    <label for="relationDescription">Description (optionnel)</label>
                    <textarea id="relationDescription" rows="3" placeholder="Détails sur cette relation..."></textarea>
                </div>

                <button type="submit" class="btn-submit">Créer la Relation</button>
            </form>
        </div>
    </div>

    <script src="<?= $basePath ?>/assets/js/modal.js"></script>
    <script src="<?= $basePath ?>/assets/js/step3_relations.js"></script>
    <script>
        const basePath = '<?= $basePath ?>';
        const partyId = <?= $party['id'] ?>;
        let characters = <?= json_encode($characters) ?>;
    </script>
</body>
</html>

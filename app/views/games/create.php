<section class="game-create">
    <h1 class="form-title">Přidat novou hru</h1>

    <form class="form-container"
          action="<?= APP_BASE ?>/games/add"
          method="POST"
          enctype="multipart/form-data"
          autocomplete="off">
        
        <?= csrfField() ?>

        <?php if (!empty($errors['csrf'])): ?>
            <div class="form-error-general">
                <small class="error"><?= htmlspecialchars($errors['csrf'][0]) ?></small>
            </div>
        <?php endif; ?>

        <div class="form-row">
            <label for="title">Název hry:</label>
            <input type="text" name="title" id="title"
                   value="<?= htmlspecialchars($old['title'] ?? '') ?>" required>
            <?php if (!empty($errors['title'])): ?>
                <small class="error"><?= htmlspecialchars($errors['title'][0]) ?></small>
            <?php endif; ?>
        </div>

        <div class="form-row">
            <label>Platformy:</label>
            <div class="multiselect">
                <?php foreach ($platforms as $p): ?>
                    <label class="multiselect-option">
                        <input type="checkbox"
                               name="platforms[]"
                               value="<?= $p['id'] ?>"
                            <?= in_array($p['id'], $old['platforms'] ?? []) ? 'checked' : '' ?>>
                        <span><?= htmlspecialchars($p['name']) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
            <?php if (!empty($errors['platforms'])): ?>
                <small class="error"><?= htmlspecialchars($errors['platforms'][0]) ?></small>
            <?php endif; ?>
        </div>

        <div class="form-row">
            <label>Žánry:</label>
            <div class="multiselect">
                <?php foreach ($genres as $g): ?>
                    <label class="multiselect-option">
                        <input type="checkbox"
                               name="genres[]"
                               value="<?= $g['id'] ?>"
                            <?= in_array($g['id'], $old['genres'] ?? []) ? 'checked' : '' ?>>
                        <span><?= htmlspecialchars($g['name']) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
            <?php if (!empty($errors['genres'])): ?>
                <small class="error"><?= htmlspecialchars($errors['genres'][0]) ?></small>
            <?php endif; ?>
        </div>

        <div class="form-row">
            <label for="year">Rok vydání:</label>
            <input type="number" id="year" name="release_year"
                   min="1980" max="<?= date('Y') ?>"
                   value="<?= htmlspecialchars($old['release_year'] ?? '') ?>" required>
            <?php if (!empty($errors['release_year'])): ?>
                <small class="error"><?= htmlspecialchars($errors['release_year'][0]) ?></small>
            <?php endif; ?>
        </div>

        <div class="form-row">
            <label for="description">Krátký popis:</label>
            <textarea id="description" name="description" rows="5" required><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
            <?php if (!empty($errors['description'])): ?>
                <small class="error"><?= htmlspecialchars($errors['description'][0]) ?></small>
            <?php endif; ?>
        </div>

        <div class="form-row">
            <label for="developer">Vývojář:</label>
            <input type="text" id="developer" name="developer"
                   value="<?= htmlspecialchars($old['developer'] ?? '') ?>" required>
            <?php if (!empty($errors['developer'])): ?>
                <small class="error"><?= htmlspecialchars($errors['developer'][0]) ?></small>
            <?php endif; ?>
        </div>

        <div class="form-row">
            <label for="publisher">Vydavatel:</label>
            <input type="text" id="publisher" name="publisher"
                   value="<?= htmlspecialchars($old['publisher'] ?? '') ?>" required>
            <?php if (!empty($errors['publisher'])): ?>
                <small class="error"><?= htmlspecialchars($errors['publisher'][0]) ?></small>
            <?php endif; ?>
        </div>

        <div class="form-row">
            <label for="cover_image">Obrázek (obálka hry):</label>
            <input type="file" id="cover_image" name="cover_image" accept="image/*" required>
            <?php if (!empty($errors['cover_image'])): ?>
                <small class="error"><?= htmlspecialchars($errors['cover_image'][0]) ?></small>
            <?php endif; ?>
        </div>

        <button type="submit">Odeslat ke schválení</button>
    </form>
</section>


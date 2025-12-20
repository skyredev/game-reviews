<section class="game-create">
    <h1 class="form-title">Přidat novou hru</h1>

    <form class="form-container"
          action="<?= APP_BASE ?>/games/add"
          method="POST"
          enctype="multipart/form-data"
          autocomplete="off"
          data-validation-rules='{"title":[["required"],["string"],["min",1],["max",255]],"description":[["required"],["string"],["min",10]],"publisher":[["required"],["string"],["min",1],["max",255]],"developer":[["required"],["string"],["min",1],["max",255]],"release_year":[["required"],["year",1980]],"genres":[["required"],["array_not_empty"]],"platforms":[["required"],["array_not_empty"]],"cover_image":[["required"],["image"],["image_max_size",5242880]]}'>
        
        <?= csrfField() ?>

        <?php if (!empty($errors['csrf'])): ?>
            <div class="form-error-general">
                <?php
                $error = $errors['csrf'];
                require __DIR__ . '/../partials/errors-tooltip.php';
                ?>
            </div>
        <?php endif; ?>

        <div class="form-row">
            <label for="title">Název hry*:</label>
            <input type="text" name="title" id="title"
                   value="<?= htmlspecialchars($old['title'] ?? '') ?>" required>
            <?php
            $error = $errors['title'] ?? null;
            require __DIR__ . '/../partials/errors-tooltip.php';
            ?>
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
            <?php
            $error = $errors['platforms'] ?? null;
            require __DIR__ . '/../partials/errors-tooltip.php';
            ?>
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
            <?php
            $error = $errors['genres'] ?? null;
            require __DIR__ . '/../partials/errors-tooltip.php';
            ?>
        </div>

        <div class="form-row">
            <label for="year">Rok vydání:</label>
            <input type="number" id="year" name="release_year"
                   min="1980" max="<?= date('Y') ?>"
                   value="<?= htmlspecialchars($old['release_year'] ?? '') ?>" required>
            <?php
            $error = $errors['release_year'] ?? null;
            require __DIR__ . '/../partials/errors-tooltip.php';
            ?>
        </div>

        <div class="form-row">
            <label for="description">Krátký popis:</label>
            <textarea id="description" name="description" rows="5" required><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
            <?php
            $error = $errors['description'] ?? null;
            require __DIR__ . '/../partials/errors-tooltip.php';
            ?>
        </div>

        <div class="form-row">
            <label for="developer">Vývojář:</label>
            <input type="text" id="developer" name="developer"
                   value="<?= htmlspecialchars($old['developer'] ?? '') ?>" required>
            <?php
            $error = $errors['developer'] ?? null;
            require __DIR__ . '/../partials/errors-tooltip.php';
            ?>
        </div>

        <div class="form-row">
            <label for="publisher">Vydavatel:</label>
            <input type="text" id="publisher" name="publisher"
                   value="<?= htmlspecialchars($old['publisher'] ?? '') ?>" required>
            <?php
            $error = $errors['publisher'] ?? null;
            require __DIR__ . '/../partials/errors-tooltip.php';
            ?>
        </div>

        <div class="form-row">
            <label for="cover_image">Obrázek (obálka hry):</label>
            <input type="file" id="cover_image" name="cover_image" accept="image/*" required>
            <?php
            $error = $errors['cover_image'] ?? null;
            require __DIR__ . '/../partials/errors-tooltip.php';
            ?>
        </div>

        <button type="submit">Odeslat ke schválení</button>
    </form>
</section>


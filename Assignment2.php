<?php
declare(strict_types=1);
session_start();

/* ---bootstrap data in session ---- */
if (!isset($_SESSION['products'])) {
  $_SESSION['products'] = [
    ['id'=>1,'name'=>'Laptop','description'=>'15" display, 8GB RAM, 512GB SSD','price'=>899.99,'category'=>'Electronics'],
    ['id'=>2,'name'=>'Coffee Maker','description'=>'Automatic drip maker with timer','price'=>49.99,'category'=>'Home Appliances'],
  ];
}
$products = &$_SESSION['products'];

/* ---------- validation + flash -------- */
$errors = [];
$submitted = [];
$flash = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // basic sanitize (for re-fill)
  $submitted['name']        = isset($_POST['name']) ? trim((string)$_POST['name']) : '';
  $submitted['description'] = isset($_POST['description']) ? trim((string)$_POST['description']) : '';
  $submitted['price']       = isset($_POST['price']) ? trim((string)$_POST['price']) : '';
  $submitted['category']    = isset($_POST['category']) ? trim((string)$_POST['category']) : '';

  // rules
  if ($submitted['name'] === '')         $errors['name'] = 'Please enter a product name.';
  if ($submitted['description'] === '')  $errors['description'] = 'Please add a short description.';
  if ($submitted['price'] === '')        $errors['price'] = 'Price is required.';
  elseif (!is_numeric($submitted['price']) || (float)$submitted['price'] <= 0) {
    $errors['price'] = 'Price must be a positive number.';
  }
  if ($submitted['category'] === '')     $errors['category'] = 'Choose a category.';

  // ok → add to session and redirect (PRG)
  if (!$errors) {
    $products[] = [
      'id'          => count($products) + 1,
      'name'        => $submitted['name'],
      'description' => $submitted['description'],
      'price'       => (float)$submitted['price'],
      'category'    => $submitted['category'],
    ];
    $_SESSION['flash_success'] = 'Product added successfully!';
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
  }
}

// flash (success)
if (isset($_SESSION['flash_success'])) {
  $flash = $_SESSION['flash_success'];
  unset($_SESSION['flash_success']);
}

$categories = ['Electronics','Home Appliances','Clothing','Books','Other'];
function h(?string $s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Product Inventory</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root{
      --brand:#2D2D2D;          /* charcoal */
      --accent:#4C7CF3;         /* calm blue */
      --muted:#F6F7FA;          /* soft gray bg */
      --line:#E8ECF3;           /* hairlines */
      --radius:14px;
    }
    body{ background:var(--muted); }
    .page-wrap{ max-width:980px; margin-inline:auto; padding:40px 18px; }
    .brand{
      display:flex; align-items:center; gap:.6rem; justify-content:center; margin-bottom:20px;
    }
    .brand .box{
      width:36px; height:36px; border-radius:10px; background:#F4A26122; border:1px solid #F4A26166;
      display:grid; place-items:center; font-size:18px;
    }
    .brand h1{ font-size:clamp(22px,4vw,34px); margin:0; letter-spacing:.4px; }
    .card{
      border:1px solid var(--line); border-radius:var(--radius); box-shadow:0 1px 2px rgba(0,0,0,.03);
    }
    .card-header{
      background:#fff; border-bottom:1px solid var(--line); font-weight:600;
      border-top-left-radius:var(--radius); border-top-right-radius:var(--radius);
    }
    .table>thead th{ font-weight:600; border-bottom:1px solid var(--line)!important; }
    .table tbody tr:hover{ background:#fbfbfd; }
    .price-cell{ font-variant-numeric: tabular-nums; }
    .badge-cat{
      background:#EEF3FF; color:#2A4ECF; border:1px solid #DDE6FF; font-weight:500;
    }
    .btn-primary{
      --bs-btn-bg:var(--accent); --bs-btn-border-color:var(--accent);
      --bs-btn-hover-bg:#335fef; --bs-btn-hover-border-color:#335fef;
      border-radius:10px; padding:.56rem 1rem; font-weight:600;
    }
    .form-control, .form-select{ border-radius:10px; }
    .alert{ border-radius:12px; }
    .tiny-note{ color:#6b7280; font-size:.88rem; }
  </style>
</head>
<body>
  <div class="page-wrap">
    <header class="brand">
      <h1 class="text-dark">Product Inventory</h1>
    </header>

    <?php if ($flash): ?>
      <div class="alert alert-success mb-4"><?php echo h($flash); ?></div>
    <?php endif; ?>

    <?php if ($errors): ?>
      <div class="alert alert-danger mb-4">
        There <?php echo count($errors)===1 ? 'is 1 error' : 'are '.count($errors).' errors'; ?> in the form. Please fix them below.
      </div>
    <?php endif; ?>

    <!-- products table -->
    <section class="card mb-4">
      <div class="card-header">Product List</div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
              <tr>
                <th style="width:70px">ID</th>
                <th style="width:22%">Name</th>
                <th>Description</th>
                <th style="width:120px">Price ($)</th>
                <th style="width:180px">Category</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($products as $p): ?>
                <tr>
                  <td><?php echo h((string)$p['id']); ?></td>
                  <td class="fw-semibold"><?php echo h($p['name']); ?></td>
                  <td><?php echo h($p['description']); ?></td>
                  <td class="price-cell"><?php echo number_format((float)$p['price'], 2); ?></td>
                  <td>
                    <span class="badge rounded-pill badge-cat px-3 py-2"><?php echo h($p['category']); ?></span>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>

    <!-- form -->
    <section class="card">
      <div class="card-header">Add a New Product</div>
      <div class="card-body">
        <form method="post" action="<?php echo h($_SERVER['PHP_SELF']); ?>">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label">Product Name</label>
              <input
                type="text"
                name="name"
                class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>"
                value="<?php echo h($submitted['name'] ?? ''); ?>"
                placeholder="e.g., Wireless Mouse"
              >
              <div class="invalid-feedback"><?php echo h($errors['name'] ?? ''); ?></div>
            </div>

            <div class="col-12">
              <label class="form-label">Description</label>
              <textarea
                name="description"
                rows="3"
                class="form-control <?php echo isset($errors['description']) ? 'is-invalid' : ''; ?>"
                placeholder="A short sentence about the product"
              ><?php echo h($submitted['description'] ?? ''); ?></textarea>
              <div class="invalid-feedback"><?php echo h($errors['description'] ?? ''); ?></div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Price ($)</label>
              <input
                type="text"
                name="price"
                class="form-control <?php echo isset($errors['price']) ? 'is-invalid' : ''; ?>"
                value="<?php echo h($submitted['price'] ?? ''); ?>"
                placeholder="e.g., 19.99"
              >
              <div class="invalid-feedback"><?php echo h($errors['price'] ?? ''); ?></div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Category</label>
              <select
                name="category"
                class="form-select <?php echo isset($errors['category']) ? 'is-invalid' : ''; ?>"
              >
                <option value="">— Select —</option>
                <?php foreach ($categories as $cat): ?>
                  <option value="<?php echo h($cat); ?>"
                    <?php echo (!empty($submitted['category']) && $submitted['category'] === $cat) ? 'selected' : ''; ?>>
                    <?php echo h($cat); ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <div class="invalid-feedback"><?php echo h($errors['category'] ?? ''); ?></div>
            </div>

            <div class="col-12">
              <button class="btn btn-primary" type="submit">Add Product</button>
            </div>
          </div>
        </form>
      </div>
    </section>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// بيانات الدخول للمشرف
$admin_username = "admin"; // اسم المستخدم
$admin_password = "password"; // كلمة المرور

// بدء الجلسة
session_start();

// التحقق من تسجيل الدخول
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    if ($username === $admin_username && $password === $admin_password) {
        $_SESSION['logged_in'] = true;
        header("Location: " . $_SERVER['PHP_SELF']); // إعادة التوجيه بعد الدخول
        exit;
    } else {
        $msg = "اسم المستخدم أو كلمة المرور غير صحيحة.";
    }
}

// تسجيل الخروج
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// إذا لم يكن مسجلًا دخول، إظهار نموذج الدخول
if (!isset($_SESSION['logged_in'])):
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تسجيل الدخول</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container" style="max-width: 400px; margin-top: 100px;">
    <div class="card p-4">
        <h3 class="text-center">تسجيل الدخول</h3>
        <?php if (isset($msg)): ?>
            <div class="alert alert-danger"><?php echo $msg; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">اسم المستخدم</label>
                <input type="text" class="form-control" name="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">كلمة المرور</label>
                <input type="password" class="form-control" name="password" required>
            </div>
            <button type="submit" name="login" class="btn btn-primary w-100">دخول</button>
        </form>
    </div>
</div>
</body>
</html>
<?php exit; endif;

// لوحة التحكم بعد الدخول
$uploadDir = __DIR__ . "/uploads/";
$uploadUrl = "uploads/";

if (!is_dir($uploadDir)) mkdir($uploadDir);

// رفع ملف
if (isset($_POST["upload"]) && isset($_FILES["file"])) {
    $file = $_FILES["file"];
    $filename = basename($file["name"]);
    $targetFile = $uploadDir . $filename;
    $ext = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    $allowed = ["php", "html"];
    $maxSize = 5 * 1024 * 1024;

    if ($file["size"] > $maxSize) {
        $msg = "الملف كبير جداً (الحد الأقصى 5MB).";
    } elseif (in_array($ext, $allowed)) {
        if (move_uploaded_file($file["tmp_name"], $targetFile)) {
            $msg = "تم رفع الملف بنجاح.";
        } else {
            $msg = "فشل في رفع الملف.";
        }
    } else {
        $msg = "فقط ملفات PHP و HTML مسموح برفعها.";
    }
}

// حذف ملف
if (isset($_GET["delete"])) {
    $file = basename($_GET["delete"]);
    if (file_exists($uploadDir . $file)) {
        unlink($uploadDir . $file);
        $msg = "تم حذف الملف.";
    } else {
        $msg = "الملف غير موجود.";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>لوحة التحكم</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <style>
    body { background: #eef2f7; font-family: 'Cairo', sans-serif; }
    .container { max-width: 750px; margin-top: 40px; }
    .custom-card {
      background: #fff;
      border-radius: 15px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      padding: 30px;
    }
    .navbar { background-color: #0056b3; }
    .navbar-brand { color: white; font-weight: bold; }
  </style>
  <script>
    function copyLink(link) {
      navigator.clipboard.writeText(link).then(() => alert("تم نسخ الرابط"));
    }
  </script>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container">
    <a class="navbar-brand" href="#">لوحة رفع البوتات</a>
    <a href="?logout=1" class="btn btn-light btn-sm">تسجيل الخروج</a>
  </div>
</nav>

<div class="container">
  <div class="custom-card">
    <h2 class="text-center mb-4"><i class="bi bi-cloud-upload-fill"></i> رفع ملفات البوت</h2>

    <?php if (isset($msg)): ?>
      <div class="alert alert-info text-center"><?php echo $msg; ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="mb-4">
      <div class="input-group">
        <input type="file" name="file" class="form-control" required>
        <button type="submit" name="upload" class="btn btn-primary"><i class="bi bi-upload"></i> رفع</button>
      </div>
    </form>

    <h5 class="mb-3"><i class="bi bi-folder2-open"></i> الملفات المرفوعة</h5>
    <div class="table-responsive">
      <table class="table table-bordered text-center align-middle">
        <thead class="table-light">
          <tr>
            <th>الاسم</th>
            <th>فتح</th>
            <th>نسخ الرابط</th>
            <th>حذف</th>
          </tr>
        </thead>
        <tbody>
        <?php
        $files = array_diff(scandir($uploadDir), ['.', '..']);
        foreach ($files as $file):
            $link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]/$uploadUrl" . rawurlencode($file);
        ?>
          <tr>
            <td><?php echo htmlspecialchars($file); ?></td>
            <td><a href="<?php echo $uploadUrl . rawurlencode($file); ?>" class="btn btn-success btn-sm" target="_blank"><i class="bi bi-box-arrow-up-right"></i></a></td>
            <td><button onclick="copyLink('<?php echo $link; ?>')" class="btn btn-secondary btn-sm"><i class="bi bi-clipboard"></i></button></td>
            <td><a href="?delete=<?php echo urlencode($file); ?>" onclick="return confirm('هل تريد حذف الملف؟')" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></a></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</body>
</html>
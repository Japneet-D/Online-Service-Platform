<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <h2>Sign In</h2>
        <form action="index.php?route=auth_login" method="POST">
            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Sign In</button>
        </form>
        <p class="mt-3">Don't have an account? <a href="index.php?route=signup">Sign Up here</a></p>
    </div>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
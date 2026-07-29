<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <h2>Sign Up</h2>
        <form action="index.php?route=auth_signup" method="POST">
            <div class="mb-3">
                <label>Full Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Address</label>
                <textarea name="address" class="form-control" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Sign Up</button>
        </form>
        <p class="mt-3">Already have an account? <a href="index.php?route=signin">Sign In here</a></p>
    </div>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
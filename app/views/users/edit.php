<?php 
$title = 'Edit User - Payroll Management System';
include __DIR__ . '/../layout/header.php'; 
?>

<div class="max-w-2xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center">
            <a href="/users" class="text-gray-500 hover:text-gray-700 mr-4">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Edit User</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Update user information and permissions
                </p>
            </div>
        </div>
    </div>

    <!-- User Form -->
    <form method="POST" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        
        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">User Information</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-2">Username *</label>
                        <input type="text" name="username" id="username" required
                               value="<?php echo htmlspecialchars($user['username']); ?>"
                               class="form-input <?php echo isset($errors['username']) ? 'border-red-300' : ''; ?>">
                        <?php if (isset($errors['username'])): ?>
                            <p class="error-message"><?php echo $errors['username']; ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                        <input type="email" name="email" id="email" required
                               value="<?php echo htmlspecialchars($user['email']); ?>"
                               class="form-input <?php echo isset($errors['email']) ? 'border-red-300' : ''; ?>">
                        <?php if (isset($errors['email'])): ?>
                            <p class="error-message"><?php echo $errors['email']; ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="full_name" class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                        <input type="text" name="full_name" id="full_name" required
                               value="<?php echo htmlspecialchars($user['full_name']); ?>"
                               class="form-input <?php echo isset($errors['full_name']) ? 'border-red-300' : ''; ?>">
                        <?php if (isset($errors['full_name'])): ?>
                            <p class="error-message"><?php echo $errors['full_name']; ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                        <input type="password" name="password" id="password" minlength="6"
                               class="form-input <?php echo isset($errors['password']) ? 'border-red-300' : ''; ?>">
                        <p class="text-xs text-gray-500 mt-1">Leave empty to keep current password</p>
                        <?php if (isset($errors['password'])): ?>
                            <p class="error-message"><?php echo $errors['password']; ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <label for="role_id" class="block text-sm font-medium text-gray-700 mb-2">Role *</label>
                        <select name="role_id" id="role_id" required class="form-select">
                            <option value="">Select Role</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?php echo $role['id']; ?>" 
                                        <?php echo $user['role_id'] == $role['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($role['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['role_id'])): ?>
                            <p class="error-message"><?php echo $errors['role_id']; ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="active" <?php echo $user['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo $user['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Information -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Account Information</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Created</label>
                        <p class="mt-1 text-sm text-gray-900">
                            <?php echo date('M j, Y H:i', strtotime($user['created_at'])); ?>
                        </p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Last Login</label>
                        <p class="mt-1 text-sm text-gray-900">
                            <?php echo $user['last_login'] ? date('M j, Y H:i', strtotime($user['last_login'])) : 'Never'; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-end space-x-4">
            <a href="/users" class="btn btn-outline">
                <i class="fas fa-times mr-2"></i>
                Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-2"></i>
                Update User
            </button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
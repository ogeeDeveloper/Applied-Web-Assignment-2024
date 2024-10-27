## Example View with Flash Messages
``` php
<!DOCTYPE html>
<html>
<head>
    <title><?= $pageTitle ?></title>
    <!-- ... other head elements ... -->
</head>
<body>
    <?php
    $flash = $this->getFlashMessages();
    if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] ?>" role="alert">
            <?= htmlspecialchars($flash['message']) ?>
        </div>
    <?php endif; ?>

    <!-- Form with old input preservation -->
    <form method="POST" action="/submit">
        <input type="text" 
               name="username" 
               value="<?= htmlspecialchars($this->old('username', '')) ?>"
               class="form-control">
        <!-- ... rest of your form ... -->
    </form>

    <?= $content ?>
</body>
</html>
```

## Controller Redirect Usage Examples
``` php
<?php
class ProductController extends BaseController {
    public function store(): void {
        try {
            $input = $this->validateInput([
                'name' => 'string',
                'price' => 'float',
                'description' => 'string'
            ]);

            $result = $this->productModel->create($input);

            if ($result['success']) {
                $this->redirect(
                    '/products',
                    'Product created successfully',
                    'success',
                    ['product_id' => $result['id']]
                );
            } else {
                // Redirect back with input preserved
                $this->redirectWithInput(
                    '/products/create',
                    $input,
                    'Failed to create product'
                );
            }
        } catch (Exception $e) {
            $this->logger->error("Product creation error: " . $e->getMessage());
            $this->redirectWithInput(
                '/products/create',
                $_POST,
                'An error occurred while creating the product'
            );
        }
    }

    public function update(int $id): void {
        try {
            $input = $this->validateInput([
                'name' => 'string',
                'price' => 'float'
            ]);

            $result = $this->productModel->update($id, $input);

            if ($result['success']) {
                $this->redirect(
                    "/products/{$id}",
                    'Product updated successfully'
                );
            } else {
                $this->redirectBack(
                    'Failed to update product',
                    'error',
                    ['validation_errors' => $result['errors']]
                );
            }
        } catch (Exception $e) {
            $this->logger->error("Product update error: " . $e->getMessage());
            $this->redirectBack('An error occurred while updating the product', 'error');
        }
    }
}
```

## Example Usage of Flash Message
``` php
// Success message
$this->redirect('/products', 'Product created successfully', 'success');

// Error message with extra data
$this->redirect('/products/create', 'Validation failed', 'error', [
    'fields' => 'Please check the required fields'
]);

// Warning message
$this->redirect('/dashboard', 'Your subscription is expiring soon', 'warning');

// For AJAX responses
$this->jsonResponse([
    'success' => true,
    'message' => 'Operation completed',
    'type' => 'success'
]);
```
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle); ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="images/favicon/favicon-16x16.png" />
    
    <!-- Styles -->
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/nouislider.min.css">
    <link rel="stylesheet" href="/css/swiper-bundle.min.css">
    <link rel="stylesheet" href="/css/venobox.css">
    <link rel="stylesheet" href="/lib/css/swiper-bundle.min.css" />
    <link rel="stylesheet" href="/lib/css/bvselect.css" />

    <!-- Add this for flash messages styling -->
    <style>
        .flash-messages {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
            max-width: 350px;
        }

        .flash-message {
            margin-bottom: 10px;
            padding: 15px;
            border-radius: 4px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            animation: slideIn 0.5s ease-out;
        }

        .flash-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }

        .flash-error {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }

        .flash-warning {
            background-color: #fff3cd;
            border-color: #ffeeba;
            color: #856404;
        }

        .flash-info {
            background-color: #cce5ff;
            border-color: #b8daff;
            color: #004085;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .flash-close {
            float: right;
            font-size: 1.5rem;
            font-weight: 700;
            line-height: 1;
            color: inherit;
            text-shadow: 0 1px 0 #fff;
            opacity: .5;
            background: none;
            border: none;
            padding: 0;
            margin: -5px 0 0 0;
        }

        .flash-close:hover {
            opacity: .75;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <?php require_once APP_ROOT . '/src/Views/shared/header.php'; ?>

    <!-- Flash Messages -->
    <?php 
    $flash = $this->getFlashMessages();
    if ($flash): 
        $typeClasses = [
            'success' => 'flash-success',
            'error' => 'flash-error',
            'warning' => 'flash-warning',
            'info' => 'flash-info'
        ];
        $type = $flash['type'] ?? 'info';
        $typeClass = $typeClasses[$type] ?? 'flash-info';
    ?>
    <div class="flash-messages">
        <div class="flash-message <?= $typeClass ?>" role="alert">
            <button type="button" class="flash-close" onclick="this.parentElement.remove();">&times;</button>
            <?= htmlspecialchars($flash['message']) ?>
            <?php if (!empty($flash['data'])): ?>
                <div class="flash-details mt-2">
                    <?php foreach ($flash['data'] as $key => $value): ?>
                        <?php if (is_string($value)): ?>
                            <div class="flash-detail">
                                <?= htmlspecialchars($key) ?>: <?= htmlspecialchars($value) ?>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <main>
        <?= $content ?>
    </main>

    <?php require_once APP_ROOT . '/src/Views/shared/footer.php'; ?>

    <!-- Scripts -->
    <!-- <script src="/lib/js/jquery.min.js"></script>
    <script src="/lib/js/bootstrap.bundle.min.js"></script>
    <script src="/lib/js/swiper-bundle.min.js"></script>
    <script src="/lib/js/main.js"></script> -->

    <!-- Flash Messages Auto-hide -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const flashMessages = document.querySelectorAll('.flash-message');
        flashMessages.forEach(function(message) {
            // Auto-hide after 5 seconds
            setTimeout(function() {
                message.style.animation = 'slideOut 0.5s ease-in forwards';
                setTimeout(function() {
                    message.remove();
                }, 500);
            }, 5000);
        });
    });
    </script>

    <!-- For AJAX requests -->
    <script>
    function showFlashMessage(message, type = 'info') {
        const flashMessagesContainer = document.querySelector('.flash-messages') || 
            (function() {
                const div = document.createElement('div');
                div.className = 'flash-messages';
                document.body.appendChild(div);
                return div;
            })();

        const messageDiv = document.createElement('div');
        messageDiv.className = `flash-message flash-${type}`;
        messageDiv.role = 'alert';

        const closeButton = document.createElement('button');
        closeButton.className = 'flash-close';
        closeButton.innerHTML = '&times;';
        closeButton.onclick = () => messageDiv.remove();

        messageDiv.appendChild(closeButton);
        messageDiv.appendChild(document.createTextNode(message));

        flashMessagesContainer.appendChild(messageDiv);

        setTimeout(() => {
            messageDiv.style.animation = 'slideOut 0.5s ease-in forwards';
            setTimeout(() => messageDiv.remove(), 500);
        }, 5000);
    }
    </script>
</body>
</html>
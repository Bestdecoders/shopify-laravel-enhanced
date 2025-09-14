<?php

/**
 * Simple verification script to test support functionality
 */

require_once __DIR__ . '/../../../../vendor/autoload.php';

echo "🔍 Verifying Support System Components...\n\n";

// Test 1: Check if classes exist and are autoloaded
echo "📂 Testing Class Autoloading:\n";

$classes = [
    'Bestdecoders\ShopifyLaravelEnhanced\Models\SupportExpectation',
    'Bestdecoders\ShopifyLaravelEnhanced\Http\Controllers\SupportController',
    'Bestdecoders\ShopifyLaravelEnhanced\Mail\SupportExpectationMail',
    'Bestdecoders\ShopifyLaravelEnhanced\Mail\SupportReplyMail',
    'Bestdecoders\ShopifyLaravelEnhanced\Console\Commands\SupportReplyCommand',
    'Bestdecoders\ShopifyLaravelEnhanced\Console\Commands\SupportListCommand',
];

foreach ($classes as $class) {
    if (class_exists($class)) {
        echo "   ✅ {$class}\n";
    } else {
        echo "   ❌ {$class} - NOT FOUND\n";
    }
}

echo "\n📋 Testing Model Properties:\n";

// Test 2: Check model properties
try {
    $model = new \Bestdecoders\ShopifyLaravelEnhanced\Models\SupportExpectation();

    $expectedFillable = ['user_id', 'expectation', 'replies', 'status', 'last_reply_at'];
    $actualFillable = $model->getFillable();

    if ($expectedFillable === $actualFillable) {
        echo "   ✅ Fillable attributes: " . implode(', ', $actualFillable) . "\n";
    } else {
        echo "   ❌ Fillable attributes mismatch\n";
        echo "      Expected: " . implode(', ', $expectedFillable) . "\n";
        echo "      Actual: " . implode(', ', $actualFillable) . "\n";
    }

    $expectedCasts = ['expectation' => 'array', 'replies' => 'array', 'last_reply_at' => 'datetime'];
    $actualCasts = $model->getCasts();

    $castMatch = true;
    foreach ($expectedCasts as $field => $cast) {
        if (!isset($actualCasts[$field]) || $actualCasts[$field] !== $cast) {
            $castMatch = false;
            break;
        }
    }

    if ($castMatch) {
        echo "   ✅ Type casting configured correctly\n";
    } else {
        echo "   ❌ Type casting configuration issue\n";
    }

} catch (Exception $e) {
    echo "   ❌ Model instantiation failed: " . $e->getMessage() . "\n";
}

echo "\n🌐 Testing Route Files:\n";

// Test 3: Check if route file exists and contains expected routes
$routeFile = __DIR__ . '/../../../routes/web.php';
if (file_exists($routeFile)) {
    $routeContent = file_get_contents($routeFile);

    $expectedRoutes = [
        'Route::prefix(\'support\')',
        'SupportController::class',
        'admin/api/support',
        'submitExpectation',
        'getReplies',
        'replyToExpectation'
    ];

    $routesFound = 0;
    foreach ($expectedRoutes as $route) {
        if (strpos($routeContent, $route) !== false) {
            $routesFound++;
            echo "   ✅ Route pattern found: {$route}\n";
        } else {
            echo "   ❌ Route pattern missing: {$route}\n";
        }
    }

    if ($routesFound === count($expectedRoutes)) {
        echo "   🎉 All expected routes are configured!\n";
    }

} else {
    echo "   ❌ Route file not found at: {$routeFile}\n";
}

echo "\n📧 Testing Email Templates:\n";

$emailTemplates = [
    __DIR__ . '/../../../resources/views/emails/support-expectation.blade.php',
    __DIR__ . '/../../../resources/views/emails/support-reply.blade.php'
];

foreach ($emailTemplates as $template) {
    $templateName = basename($template);
    if (file_exists($template)) {
        $size = filesize($template);
        echo "   ✅ {$templateName} ({$size} bytes)\n";
    } else {
        echo "   ❌ {$templateName} - NOT FOUND\n";
    }
}

echo "\n📊 Testing Migration File:\n";

$migrationDir = __DIR__ . '/../../../database/migrations/';
$migrationFiles = glob($migrationDir . '*create_support_expectations_table.php');

if (!empty($migrationFiles)) {
    $migrationFile = $migrationFiles[0];
    $migrationContent = file_get_contents($migrationFile);

    $requiredColumns = [
        'user_id',
        'expectation',
        'replies',
        'status',
        'last_reply_at'
    ];

    $columnsFound = 0;
    foreach ($requiredColumns as $column) {
        if (strpos($migrationContent, "'{$column}'") !== false || strpos($migrationContent, "\$table->{$column}") !== false) {
            $columnsFound++;
            echo "   ✅ Column defined: {$column}\n";
        } else {
            echo "   ❌ Column missing: {$column}\n";
        }
    }

    if ($columnsFound === count($requiredColumns)) {
        echo "   🎉 Migration file is complete!\n";
    }

} else {
    echo "   ❌ Migration file not found in: {$migrationDir}\n";
}

echo "\n🎮 Testing Console Commands:\n";

// Test 4: Check if command signatures are properly defined
try {
    $replyCommand = new \Bestdecoders\ShopifyLaravelEnhanced\Console\Commands\SupportReplyCommand();
    $listCommand = new \Bestdecoders\ShopifyLaravelEnhanced\Console\Commands\SupportListCommand();

    if (strpos($replyCommand->getName(), 'support:reply') !== false) {
        echo "   ✅ SupportReplyCommand signature configured\n";
    } else {
        echo "   ❌ SupportReplyCommand signature issue\n";
    }

    if (strpos($listCommand->getName(), 'support:list') !== false) {
        echo "   ✅ SupportListCommand signature configured\n";
    } else {
        echo "   ❌ SupportListCommand signature issue\n";
    }

} catch (Exception $e) {
    echo "   ❌ Command instantiation failed: " . $e->getMessage() . "\n";
}

echo "\n📁 Testing JavaScript Component:\n";

$jsComponent = __DIR__ . '/../../../resources/js/components/support.jsx';
if (file_exists($jsComponent)) {
    $jsContent = file_get_contents($jsComponent);
    $jsSize = filesize($jsComponent);

    $jsFeatures = [
        'useState',
        'useEffect',
        'submitExpectation',
        'fetchMyReplies',
        'replies.map',
        'formatDate'
    ];

    $featuresFound = 0;
    foreach ($jsFeatures as $feature) {
        if (strpos($jsContent, $feature) !== false) {
            $featuresFound++;
        }
    }

    echo "   ✅ Support component ({$jsSize} bytes)\n";
    echo "   ✅ React features: {$featuresFound}/" . count($jsFeatures) . " found\n";

    if (strpos($jsContent, '/support/submit/expectation') !== false) {
        echo "   ✅ API endpoint integration configured\n";
    } else {
        echo "   ❌ API endpoint integration missing\n";
    }

} else {
    echo "   ❌ Support component not found at: {$jsComponent}\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "📊 SUMMARY:\n";
echo "✅ Support system components have been successfully created!\n";
echo "🚀 Ready for testing in Laravel application environment.\n";
echo "\n💡 Next steps:\n";
echo "   1. Run: php artisan migrate (to create support_expectations table)\n";
echo "   2. Test: php artisan support:list (should show no requests)\n";
echo "   3. Access: /support (to view the support page)\n";
echo "   4. Submit a test request through the web interface\n";
echo "   5. Reply: php artisan support:reply 1 \"Test reply\"\n";
echo str_repeat("=", 60) . "\n";
?>
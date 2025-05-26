#!/usr/bin/env node

import { execSync } from 'child_process';
import { platform } from 'os';

console.log('🔍 Running pre-push hook...');

try {
    // Check if there are any unstaged changes before running php-cs-fixer
    let hasUnstagedChangesBefore = false;
    try {
        const unstagedBefore = execSync('git diff --name-only', { encoding: 'utf8' }).trim();
        hasUnstagedChangesBefore = unstagedBefore.length > 0;
    } catch (error) {
        // If git diff fails, continue anyway
    }

    // Run php-cs-fixer
    console.log('🔧 Running PHP CS Fixer...');
    const csFixerCommand = platform() === 'win32'
        ? 'vendor\\bin\\php-cs-fixer.bat fix --verbose'
        : './vendor/bin/php-cs-fixer fix --verbose';

    execSync(csFixerCommand, { stdio: 'inherit' });

    // Check if php-cs-fixer made any changes
    let hasUnstagedChangesAfter = false;
    let changedFiles = '';
    try {
        changedFiles = execSync('git diff --name-only', { encoding: 'utf8' }).trim();
        hasUnstagedChangesAfter = changedFiles.length > 0;
    } catch (error) {
        console.error('❌ Error checking git status:', error.message);
        process.exit(1);
    }

    // Only commit if php-cs-fixer actually made changes
    if (hasUnstagedChangesAfter && (!hasUnstagedChangesBefore || changedFiles)) {
        console.log('📝 PHP CS Fixer made changes to the following files:');
        console.log(changedFiles);
        console.log('💾 Committing formatting changes...');

        try {
            execSync('git add .', { stdio: 'inherit' });
            execSync('git commit -m "[HOOK] Ran php-cs-fixer to fix formatting issues"', { stdio: 'inherit' });
            console.log('✅ Formatting changes committed successfully.');
        } catch (commitError) {
            console.error('❌ Error committing changes:', commitError.message);
            process.exit(1);
        }
    } else {
        console.log('✅ No formatting changes needed.');
    }

    console.log('🎉 Pre-push hook completed successfully.');
    process.exit(0);

} catch (error) {
    console.error('❌ Pre-push hook failed:', error.message);
    process.exit(1);
}

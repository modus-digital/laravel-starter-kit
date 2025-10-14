<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

?>

<div class="flex flex-col items-center justify-center px-6 py-8 mx-auto md:h-screen lg:py-0">
     <?php $__env->slot('title', null, []); ?> <?php echo e(__('auth.login.page_title')); ?> <?php $__env->endSlot(); ?>

    
    <a href="#" class="flex items-center mb-6 text-2xl font-semibold text-zinc-900 dark:text-white">
        <?php if (isset($component)) { $__componentOriginal8892e718f3d0d7a916180885c6f012e7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8892e718f3d0d7a916180885c6f012e7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.application-logo','data' => ['class' => 'w-24 h-24 mr-3 text-zinc-900 dark:text-zinc-50']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('application-logo'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-24 h-24 mr-3 text-zinc-900 dark:text-zinc-50']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8892e718f3d0d7a916180885c6f012e7)): ?>
<?php $attributes = $__attributesOriginal8892e718f3d0d7a916180885c6f012e7; ?>
<?php unset($__attributesOriginal8892e718f3d0d7a916180885c6f012e7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8892e718f3d0d7a916180885c6f012e7)): ?>
<?php $component = $__componentOriginal8892e718f3d0d7a916180885c6f012e7; ?>
<?php unset($__componentOriginal8892e718f3d0d7a916180885c6f012e7); ?>
<?php endif; ?>
    </a>

    
    <div class="w-full bg-white rounded-lg shadow dark:border md:mt-0 sm:max-w-md xl:p-0 dark:bg-zinc-800 dark:border-zinc-700">
        <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
            <h1 class="text-xl font-bold leading-tight tracking-tight text-zinc-900 md:text-2xl dark:text-white">
                <?php echo e(__('auth.login.title')); ?>

            </h1>

            <form class="space-y-4 md:space-y-6" wire:submit="authenticate">
                
                <div>
                    <label for="email" class="block mb-2 text-sm font-medium text-zinc-900 dark:text-white"><?php echo e(__('auth.login.email')); ?></label>
                    <input wire:model="email" type="email" name="email" id="email" class="bg-zinc-50 border border-zinc-300 text-zinc-900 rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-zinc-700 dark:border-zinc-600 dark:placeholder-zinc-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="<?php echo e(__('common.placeholders.email')); ?>">
                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-sm text-red-500"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                </div>

                
                <div>
                    <label for="password" class="block mb-2 text-sm font-medium text-zinc-900 dark:text-white"><?php echo e(__('auth.login.password')); ?></label>
                    <input wire:model="password" type="password" name="password" id="password" placeholder="<?php echo e(__('common.placeholders.password')); ?>" class="bg-zinc-50 border border-zinc-300 text-zinc-900 rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-zinc-700 dark:border-zinc-600 dark:placeholder-zinc-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-sm text-red-500"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                </div>

                
                <div class="flex items-center justify-between">
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input wire:model="remember" id="remember" aria-describedby="remember" type="checkbox" class="w-4 h-4 border border-zinc-300 rounded bg-zinc-50 focus:ring-3 focus:ring-primary-300 dark:bg-zinc-700 dark:border-zinc-600 dark:focus:ring-primary-600 dark:ring-offset-zinc-800">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="remember" class="text-zinc-500 dark:text-zinc-300"><?php echo e(__('auth.login.remember')); ?></label>
                        </div>
                    </div>

                    <a href="<?php echo e(route('password.forgot')); ?>" class="text-sm font-medium text-primary-600 hover:underline dark:text-primary-500"><?php echo e(__('auth.login.forgot_password')); ?></a>

                </div>

                

                <button type="submit" class="w-full text-white bg-primary-600 hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800"><?php echo e(__('auth.login.sign_in')); ?></button>
                <p class="text-sm font-light text-zinc-500 dark:text-zinc-400">
                    <?php echo e(__('auth.login.no_account')); ?> <a href="<?php echo e(route('register')); ?>" wire:navigate class="font-medium text-primary-600 hover:underline dark:text-primary-500"><?php echo e(__('auth.login.sign_up')); ?></a>
                </p>
            </form>
        </div>
    </div>
</div><?php /**PATH E:\projects\starter-kits\modus-starter-kit-v2\resources\views\livewire/auth/login.blade.php ENDPATH**/ ?>
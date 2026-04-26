<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Product;

class ProductPolicy
{
    /**
     * Determine if the user can view any products.
     */
    public function viewAny(User $user): bool
    {
        return true; // Both admin and employees can view
    }

    /**
     * Determine if the user can view a specific product.
     */
    public function view(User $user, Product $product): bool
    {
        return true;
    }

    /**
     * Determine if the user can create products.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin(); // Only admins can create products
    }

    /**
     * Determine if the user can update a product.
     */
    public function update(User $user, Product $product): bool
    {
        return $user->isAdmin(); // Only admins can update products
    }

    /**
     * Determine if the user can delete a product.
     */
    public function delete(User $user, Product $product): bool
    {
        return $user->isAdmin(); // Only admins can delete products
    }
}

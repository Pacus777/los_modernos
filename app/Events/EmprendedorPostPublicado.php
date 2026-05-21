<?php

namespace App\Events;

use App\Models\EmprendedorPost;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Post del emprendedor visible en el feed (S4-02).
 */
class EmprendedorPostPublicado
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public EmprendedorPost $post,
    ) {}
}

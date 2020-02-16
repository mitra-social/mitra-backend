<?php

declare(strict_types=1);

namespace Mitra\Validator\Symfony\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
final class Valid extends Constraint
{
}

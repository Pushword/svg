<?php

namespace Pushword\Svg;

use Exception;
use PiedWeb\RenderAttributes\AttributesTrait;
use Pushword\Core\AutowiringTrait\RequiredApps;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TwigExtension extends AbstractExtension
{
    use AttributesTrait;
    use RequiredApps;

    public function getFunctions()
    {
        return [
            new TwigFunction('svg', [$this, 'getSvg'], ['needs_environment' => false, 'is_safe' => ['html']]),
        ];
    }

    /**
     * @param array<string, string> $attr
     */
    public function getSvg(string $name, array $attr = ['class' => 'fill-current w-4 inline-block -mt-1'], string $dir = ''): string
    {
        $dirs = '' !== $dir ? $dir : $this->apps->get()->get('svg_dir');

        if (! \is_array($dirs)) {
            $dirs = [$dirs];
        }

        $file = null;
        foreach ($dirs as $dirPath) {
            $file = $dirPath.'/'.$name.'.svg';
            if (file_exists($file)) {
                break;
            }
            $file = null;
        }

        if (null === $file) {
            throw new Exception('`'.$name.'` (svg) not found.');
        }

        if (! \in_array(mime_content_type($file), ['image/svg+xml', 'image/svg'], true)
            || ($svg = file_get_contents($file)) === false) {
            throw new Exception('`'.$name.'` seems not be a valid svg file.');
        }

        $svg = self::replaceOnce('<svg ', '<svg '.self::mapAttributes($attr).' ', $svg);

        return $svg;
    }

    private static function replaceOnce(string $needle, string $replace, string $haystack): string
    {
        $pos = strpos($haystack, $needle);
        if (false !== $pos) {
            $haystack = substr_replace($haystack, $replace, $pos, \strlen($needle));
        }

        return $haystack;
    }
}

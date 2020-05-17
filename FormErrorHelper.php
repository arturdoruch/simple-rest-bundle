<?php

namespace ArturDoruch\SimpleRestBundle;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class FormErrorHelper
{
    /**
     * @var TranslatorInterface
     */
    private static $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public static function setTranslator(TranslatorInterface $translator)
    {
        self::$translator = $translator;
    }

    /**
     * Gets formatted form error messages.
     *
     * @param FormInterface $form
     * @param bool $flatten Whether to flatten error messages multidimensional array into simple array
     *                      with key (form names path) value (messages concatenated with ";") pairs.
     *                      E.g. "name.formLevel2Name" => "message1; message2"
     *
     * @return array
     */
    public static function getMessages(FormInterface $form, bool $flatten = false): array
    {
        $messages = self::doGetMessages($form);

        if ($flatten) {
            return self::flattenMessages($messages);
        }

        return $messages;
    }

    /**
     * Gets formatted form error messages.
     *
     * @param FormInterface $form
     *
     * @return array
     */
    private static function doGetMessages(FormInterface $form): array
    {
        $messages = [];

        foreach ($form->getErrors() as $error) {
            $message = self::translateMessage($error);

            if (!$form->getExtraData() && strpos($message, 'This form should not contain extra fields') === false) {
                $messages[] = $message;
            }
        }

        foreach ($form->getExtraData() as $name => $value) {
            $messages[$name][] = 'Unknown parameter.';
        }

        $children = $form->all();

        foreach ($children as $childForm) {
            if ($childForm instanceof FormInterface) {
                if ($childErrors = self::doGetMessages($childForm)) {
                    $messages[$childForm->getName()] = $childErrors;
                }
            }
        }

        return $messages;
    }


    private static function flattenMessages(array $messages, $namePath = null): array
    {
        $flatten = [];

        foreach ($messages as $name => $message) {
            if (is_array($message)) {
                $flatten = array_merge($flatten, self::flattenMessages($message, $namePath . '.' . $name));
            } else {
                $np = $namePath !== null ? ltrim($namePath, '.') : $name;

                if (isset($flatten[$np])) {
                    $message = rtrim($flatten[$np], '.') . '; ' . $message;
                }

                $flatten[$np] = $message;
            }
        }

        return $flatten;
    }

    /**
     * @param FormError $error
     *
     * @return string
     */
    private static function translateMessage(FormError $error)
    {
        if (!self::$translator) {
            return $error->getMessage();
        }

        if (null !== $error->getMessagePluralization()) {
            return self::$translator->transChoice($error->getMessageTemplate(), $error->getMessagePluralization(), $error->getMessageParameters(), 'validators');
        }

        return self::$translator->trans($error->getMessageTemplate(), $error->getMessageParameters(), 'validators');
    }
}

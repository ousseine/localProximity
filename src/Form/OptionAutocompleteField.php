<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Option;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;

#[AsEntityAutocompleteField]
class OptionAutocompleteField extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => Option::class,
            'searchable_fields' => ['label'],
            'label' => 'Options',
            'choice_label' => 'label',
            'multiple' => true,
            'required' => false,
//            'constraints' => [
//                new Count(min: 1, minMessage: 'We need to eat *something*'),
//            ],
        ]);
    }

    public function getParent(): string
    {
        return BaseEntityAutocompleteType::class;
    }
}

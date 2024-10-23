<?php

namespace App\Form;

use App\Entity\Answer;
use App\Entity\Question;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnswerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $survey = $options['survey'];
        $questions = $survey->getQuestions();

        foreach ($questions as $question) {
            $response = 'response-' . $question->getId();

            switch ($question->getType()) {
                case 'hidden':
                    $builder->add($response, HiddenType::class, [
                        'label' => false,
                        'required' => $question->isRequired(),
                        'mapped' => false,
                        'attr' => [
                            'class' => $question->getName(),
                            'data-swup-submit-target' => 'areaFields'
                        ],
                    ]);
                    break;
                case 'text':
                    $builder->add($response, TextType::class, [
                        'label' => $question->getLabel() ? $question->getlabel() : '',
                        'required' => $question->isRequired(),
                        'mapped' => false,
                        'attr' => [
                            'class' => $question->getName(),
                            'data-swup-submit-target' => 'areaFields'
                        ],
                    ]);
                    break;
                case 'textarea':
                    $builder->add($response, TextareaType::class, [
                        'label' => $question->getLabel() ? $question->getlabel() : '',
                        'required' => $question->isRequired(),
                        'attr' => [
                            'class' => $question->getName(),
                            'rows' => 5,
                            'data-swup-submit-target' => 'areaFields'
                        ],
                        'mapped' => false,
                    ]);
                    break;
                case 'unique_choice':
                    $builder->add($response, ChoiceType::class, [
                        'label' => $question->getLabel() ? $question->getlabel() : '',
                        'required' => $question->isRequired(),
                        'choices' => $this->formatChoices($question),
                        'expanded' => true,
                        'multiple' => false,
                        'mapped' => false,
                        'attr' => [
                            'class' => $question->getName(),
                            'name' => $question->getName(),
                            'data-swup-submit-target' => 'options'
                        ],
                    ]);
                    break;
                case 'multiple_choice':
                    $builder->add($response, ChoiceType::class, [
                        'label' => $question->getLabel() ? $question->getlabel() : '',
                        'required' => $question->isRequired(),
                        'choices' => $this->formatChoices($question),
                        'expanded' => true,
                        'multiple' => true,
                        'mapped' => false,
                        'attr' => [
                            'class' => $question->getName(),
                            'data-swup-submit-target' => 'options'
                        ],
                    ]);
                    break;
            }
        }

        $builder->add('save', SubmitType::class, [
            'label' => 'Je continue',
            'attr' => ['class' => 'btn btn-primary']
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Answer::class,
            'survey' => null,
            'question' => null
        ]);
    }

    // Récupère les choix dans les options
    private function formatChoices(Question $question): array
    {
        $choices = [];
        foreach ($question->getOptions() as $option) {
            $choices[$option->getLabel()] = $option->getLabel();
        }

        return $choices;
    }
}

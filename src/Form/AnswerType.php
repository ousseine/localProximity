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
                        'label' => $question->getLabel(),
                        'required' => $question->isRequired(),
                        'attr' => ['class' => $question->getName()],
                        'mapped' => false,
                    ]);
                    break;
                case 'text':
                    $builder->add($response, TextType::class, [
                        'label' => $question->getLabel(),
                        'required' => $question->isRequired(),
                    ]);
                    break;
                case 'textarea':
                    $builder->add($response, TextareaType::class, [
                        'label' => $question->getLabel(),
                        'required' => $question->isRequired(),
                    ]);
                    break;
                case 'unique_choice':
                    $builder->add($response, ChoiceType::class, [
                        'label' => $question->getLabel(),
                        'required' => $question->isRequired(),
                        'choices' => $this->formatChoices($question),
                        'expanded' => true,
                        'multiple' => false,
                        'mapped' => false
                    ]);
                    break;
                case 'multiple_choice':
                    $builder->add($response, ChoiceType::class, [
                        'label' => $question->getLabel(),
                        'required' => $question->isRequired(),
                        'choices' => $this->formatChoices($question),
                        'expanded' => true,
                        'multiple' => true,
                    ]);
                    break;
            }
        }

        $builder->add('save', SubmitType::class, [
            'label' => 'Je continue'
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

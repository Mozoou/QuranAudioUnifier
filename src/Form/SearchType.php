<?php

namespace App\Form;

use App\Services\AudioFetcher\AudioFetcher;
use App\Services\AudioFetcher\Resources\Reciter;
use App\Services\AudioFetcher\Resources\Surah;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchType extends AbstractType
{
    public function __construct(private AudioFetcher $audioFetcher)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'choices' => $this->audioFetcher->fetchTypes(),
                'label' => false,
                'expanded' => true,
                'multiple' => false,
                'choice_label' => function (string $value) {
                    return ucfirst($value);
                },
                'attr' => [
                    'class' => 'type-radio'
                ]
            ])
            ->add('language', ChoiceType::class, [
                'choices' => $this->audioFetcher->fetchLanguages(),
                'label' => 'Language',
                'choice_label' => function (?string $value) {
                    return $value;
                },
                'choice_value' => function (?string $array) {
                },
                'attr' => [
                    'class' => 'language-select'
                ]
            ])
            ->add('reciter', ChoiceType::class, [
                'choices' => [],
                'label' => 'Reciter',
                'choice_label' => function (?Reciter $reciter) {
                    return $reciter ? strtoupper($reciter->getName()) : '';
                },
                'choice_attr' => function (?Reciter $reciter) {
                    return $reciter ? ['class' => 'category_' . strtolower($reciter->getName())] : [];
                },
                'group_by' => function (Reciter $reciter) {
                    return $reciter->getBitrate()->getName();
                },
                'attr' => [
                    'class' => 'reciter-select'
                ]
            ])
            ->add('surah', ChoiceType::class, [
                'choices' => $this->audioFetcher->fetchSuwar(),
                'label' => 'Surah',
                'choice_label' => function (?Surah $surah) {
                    return $surah ? strtoupper($surah->getName()) : '';
                },
                'choice_attr' => function (?Surah $surah) {
                    return $surah ? ['class' => 'category_' . strtolower($surah->getName())] : [];
                },
                'group_by' => function (Surah $surah) {
                    return $surah->getRevelationType();
                },
                'attr' => [
                    'class' => 'surah-select'
                ]
            ])
            ->add('verse_from', IntegerType::class, [
                'label' => 'From verse',
                'attr' => [
                    'min' => '1'
                ]
            ])
            ->add('verse_to', IntegerType::class, [
                'label' => 'To verse',
                'attr' => [
                    'class' => 'verse-to-input',
                    'max' => '7',
                ]
            ])
            ->add('generate', SubmitType::class, [
                'label' => 'Generate audio',
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}

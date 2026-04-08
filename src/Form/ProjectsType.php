<?php

namespace App\Form;

use App\Entity\Projects;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProjectsType extends AbstractType
{
    /**
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                TextType::class,
                [
                    'label' => 'Projektname',
                    'attr' => [
                        'placeholder' => 'Wie heißt das Projekt?',
                        'class' => 'form-control editor-title-input',
                    ],
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Bitte gib einen Projektnamen ein.',
                        ]),
                        new Length([
                            'min' => 3,
                            'minMessage' => 'Der Name muss mindestens {{ limit }} Zeichen haben.',
                            'max' => 4096,
                        ]),
                    ],
                ]
            )
            ->add(
                'description',
                TextareaType::class,
                [
                    'required' => false,
                    'label' => false,
                    'attr' => [
                        'class' => 'tiptap-source',
                        'data-upload-url' => '/project/upload',
                    ],
                ]
            )

            ->add('shortDescription', TextareaType::class, [
                    'label' => 'Kurzbeschreibung',
                    'required' => false,
                    'attr' => [
                        'placeholder' => 'Ein kurzer Teaser für die Projektübersicht ...',
                        'class' => 'form-control',
                        'rows' => 3,
                    ],
                ])
            ->add('id', HiddenType::class)
            ->add('previewPicture', HiddenType::class, ['required' => false])
            ->add(
                'seoLink',
                TextType::class,
                [
                    'label' => 'SEO-URL',
                    'attr' => [
                        'placeholder' => 'mein-projekt-url-slug',
                        'class' => 'form-control',
                    ],
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Bitte gib eine gültige URL ein.',
                        ]),
                    ],
                ]
            )
            ->add('link', TextType::class, [
                'label' => 'Projekt-URL',
                'required' => false,
                'attr' => [
                    'placeholder' => 'https://github.com/...',
                    'class' => 'form-control',
                ],
            ])
            ->add('originalLink', TextType::class, [
                'label' => 'Original-Quelle',
                'required' => false,
                'attr' => [
                    'placeholder' => 'https://...',
                    'class' => 'form-control',
                ],
            ])
            ->add('isPublic', null, ['label' => 'Öffentlich sichtbar'])
            ->add('projectTags', HiddenType::class, ['mapped' => false, 'required' => false])
            ->add('state', null, ['label' => 'Status'])
            ->add('created', DateTimeType::class, [
                'label'  => 'Datum',
                'widget' => 'single_text',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
        ;
    }

    /**
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Projects::class,
        ]);
    }
}

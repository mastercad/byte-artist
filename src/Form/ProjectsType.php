<?php

namespace App\Form;

use App\Entity\Projects;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProjectsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                TextType::class,
                [
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Please enter a project name',
                        ]),
                        new Length([
                            'min' => 6,
                            'minMessage' => 'The name should be at least {{ limit }} characters',
                            // max length allowed by Symfony for security reasons
                            'max' => 4096,
                        ]),
                    ],
                ]
            )
            ->add('shortDescription')
//            ->add('description')

            ->add(
                'description',
                CKEditorType::class,
                [
                    'config' => [
                //                        'filebrowserUploadUrl' => '/upload?',
                //                        'filebrowserImageUploadUrl' => '/upload?',
                        'filebrowserImageUploadRoute' => 'app_project_image_upload',
                        'filebrowserImageUploadRouteParameters' => [
                            'type' => 'project',
                            'id' => print_r($builder->getData()->getId(), true),
                        ],
                        'filebrowserBrowseRoute' => 'app_project_image_browser',
                        'filebrowserBrowseRouteParameters' => [
                            'type' => 'project',
                            'id' => print_r($builder->getData()->getId(), true),
                        ],
                    ],
                ]
            )

            ->add('id', HiddenType::class)
            ->add('previewPicture')
            ->add(
                'seoLink',
                TextType::class,
                [
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Please enter a valid project name!',
                        ]),
                    ],
                ]
            )
            ->add('link', TextType::class)
            ->add('isPublic')
            ->add('projectTags')
            ->add('originalLink', TextType::class)
            ->add('state')
            ->add('creator')
            ->add('created')
            ->add('modifier')
            ->add('modified')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Projects::class,
        ]);
    }
}

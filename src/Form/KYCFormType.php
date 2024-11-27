<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
//use App\Service\CountryService;

class KYCFormType extends AbstractType
{
    private CountryService $countryService;

    private $africanCountries = [
        "Afrique du Sud",
        "Algérie",
        "Angola",
        "Bénin",
        "Botswana",
        "Burkina Faso",
        "Burundi",
        "Cap-Vert",
        "Cameroun",
        "Comores",
        "Congo (Brazzaville)",
        "Congo (Kinshasa) - République démocratique du Congo",
        "Côte d'Ivoire",
        "Djibouti",
        "Égypte",
        "Eswatini (Swaziland)",
        "Éthiopie",
        "Gabon",
        "Gambie",
        "Ghana",
        "Guinée",
        "Guinée-Bissau",
        "Guinée équatoriale",
        "Kenya",
        "Lesotho",
        "Liberia",
        "Libye",
        "Madagascar",
        "Malawi",
        "Mali",
        "Maroc",
        "Maurice",
        "Mauritanie",
        "Mozambique",
        "Namibie",
        "Niger",
        "Nigeria",
        "Ouganda",
        "République Centrafricaine",
        "Rwanda",
        "Sao Tomé-et-Principe",
        "Sénégal",
        "Seychelles",
        "Sierra Leone",
        "Somalie",
        "Soudan",
        "Soudan du Sud",
        "Tanzanie",
        "Tchad",
        "Togo",
        "Tunisie",
        "Zambie",
        "Zimbabwe"
    ];

    



    public function __construct()
    {
        //$this->countryService = $countryService;
    }


    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Récupération des listes de pays et codes téléphoniques
        //$countriesWithCodes = $this->countryService->getCountriesWithPhoneCodes();

        $builder
            
            ->add('fullname', TextType::class, [
                'label' => 'Nom complet',
                'constraints' => [new NotBlank()],
            ])
            ->add('country', ChoiceType::class, [
                'label' => 'Pays',
                'choices' => array_flip($this->africanCountries),
                'placeholder' => 'Sélectionnez votre pays',
                'constraints' => [new NotBlank()],
            ])
            ->add('phone', TextType::class, [
                'label' => 'Telephone',
                'constraints' => [new NotBlank()],
            ])

            ->add('brithdate', DateType::class, [
                'label' => 'Date de naissance',
                'widget' => 'single_text',
                'constraints' => [new NotBlank()],
            ])
            ->add('avatar', FileType::class, [
                'label' => 'Avatar (image uniquement)',
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new File([
                        'mimeTypes' => ['image/jpeg', 'image/png'],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPG ou PNG).',
                    ]),
                ],
            ])
            ->add('humanproofimage', FileType::class, [
                'label' => 'Preuve d\'identité (image)',
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new File([
                        'mimeTypes' => ['image/jpeg', 'image/png'],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPG ou PNG).',
                    ]),
                ],
            ])
            ->add('idcard', FileType::class, [
                'label' => 'Carte d\'identité (recto)',
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new File([
                        'mimeTypes' => ['image/jpeg', 'image/png'],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPG ou PNG).',
                    ]),
                ],
            ])
            ->add('idcardback', FileType::class, [
                'label' => 'Carte d\'identité (verso)',
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new File([
                        'mimeTypes' => ['image/jpeg', 'image/png'],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPG ou PNG).',
                    ]),
                ],
            ])
            ->add('residenceproofimage', FileType::class, [
                'label' => 'Preuve de résidence (image)',
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new File([
                        'mimeTypes' => ['image/jpeg', 'image/png'],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPG ou PNG).',
                    ]),
                ],
            ]);
       
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => \App\Entity\User::class,
        ]);
    }
}

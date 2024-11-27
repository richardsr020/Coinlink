<?php

namespace App\Form;

use App\Entity\Loan;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LoanType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('amount', MoneyType::class, [
                'label' => 'Montant de l\'emprunt',
                'currency' => 'USD',
                'attr' => [
               // Ajout de la classe Bootstrap pour le style
                    'placeholder' => 'Entrez le montant souhaité',
                ],
            ])
            ->add('accountid', IntegerType::class, [
                'label' => 'ID du compte',
                'attr' => [
                 // Ajout de la classe Bootstrap pour le style
                    'placeholder' => 'Entrez l\'ID du compte',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Loan::class,
        ]);
    }
}

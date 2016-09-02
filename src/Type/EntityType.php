<?php

namespace UEC\Standalone\Symfony\Form\Doctrine\Type;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use UEC\Standalone\Symfony\Form\Doctrine\DataTransformer\CollectionToArrayTransformer;

class EntityType extends AbstractType
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * EntityType constructor.
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['multiple']) {
            $builder->addViewTransformer(new CollectionToArrayTransformer(), true);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $choicesNormalizer = function (Options $options, $choices) {
            if (empty($choices)) {
                if ($options['query_builder'] instanceof QueryBuilder) {
                    $qb = $options['query_builder'];
                } else {
                    $alias = 'obj';

                    $qb = $options['em']->createQueryBuilder();
                    $qb->select('obj');
                    $qb->from($options['class'], $alias);
                    $qb->orderBy($alias.'.'.$options['property'], 'ASC');

                    if (is_callable($options['query_builder'])) {
                        $qb = $options['query_builder']($qb, $options['class'], $alias);
                    }
                }

                $choices = $qb->getQuery()->getResult();
            }

            return $choices;
        };

        $choiceLabelNormalizer = function (Options $options, $choiceLabel) {
            if (!empty($choiceLabel)) {
                return $choiceLabel;
            }

            $property = $options['property'];

            return function ($value, $key, $index) use ($property) {
                return $value->{'get'.ucfirst($property)}();
            };
        };

        $resolver->setRequired(['property']);

        $resolver->setDefaults([
            'em' => $this->entityManager,
            'choices' => null,
            'class' => null,
            'property' => null,
            'query_builder' => null,
        ]);

        $resolver->setNormalizer('choices', $choicesNormalizer);
        $resolver->setNormalizer('choice_label', $choiceLabelNormalizer);
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
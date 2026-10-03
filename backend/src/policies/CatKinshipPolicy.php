<?php

namespace CatRegistry\applications\policies;

use CatRegistry\applications\repositories\CatRegistryRepositoryInterface;
use yii\web\BadRequestHttpException;

final readonly class CatKinshipPolicy
{
    public function __construct(
        private readonly CatRegistryRepositoryInterface $repository,
    ) {
    }

    /**
     * Проверяет, что указанные родители допустимы для кошки.
     *
     * @param ?int $catId id текущей кошки; null при создании, int при обновлении
     * @param ?int $motherId  id матери; null, если мать не указана (валидный случай)
     * @param array<int> $fatherIds список id отцов; пустой массив, если отцов нет (валидный случай)
     *
     * @throws BadRequestHttpException
     */
    public function ensureCanAssignParents(
        ?int $catId,
        ?int $motherId,
        array $fatherIds,
    ) {
        // Кошка не может быть своим родителем.
        $this->ensureNoSelfParenting($catId, $motherId, $fatherIds);

        // Пол родителей должен соответствовать роли.
        $this->ensureMotherIsFemale($motherId);
        $this->ensureFathersAreMale($fatherIds);

        // Граф родства не должен зацикливаться.
        $this->ensureNoCycle($catId, $motherId, $fatherIds);
    }

    /**
     * Проверяет, что кошка не назначается сама себе матерью или отцом.
     *
     * Актуально только для обновления: при создании id ещё нет.
     *
     * @param ?int $catId
     * @param ?int $motherId
     * @param array<int> $fatherIds
     * @return void
     */
    private function ensureNoSelfParenting(
        ?int $catId,
        ?int $motherId,
        array $fatherIds,
    ): void {
        if ($catId === null) {
            return;
        }

        if ($motherId === $catId) {
            throw new BadRequestHttpException(null, [
                'mother_id' => 'Кошка не может быть своей матерью.',
            ]);
        }

        if (in_array($catId, $fatherIds, true)) {
            throw new BadRequestHttpException(null, [
                'father_ids' => 'Кошка не может быть своим отцом.',
            ]);
        }
    }

    /**
     * Проверяет, что мать — самка.
     *
     * Если мать не указана — правило неприменимо, пропускаем.
     *
     * @param ?int $motherId
     * @return void
     */
    private function ensureMotherIsFemale(?int $motherId): void
    {
        if ($motherId === null) {
            return;
        }

        $mother = $this->repository->findId($motherId);
        if ($mother === null || $mother->gender !== 'female') {
            throw new BadRequestHttpException(null, [
                'mother_id' => 'Мать должна быть женского пола.',
            ]);
        }
    }

    /**
     * Проверяет, что все отцы — самцы.
     *
     * Если отцов нет — правило неприменимо, цикл не выполнится.
     * @param array<int> $fatherIds
     * @return void
     */
    private function ensureFathersAreMale(array $fatherIds): void
    {
        foreach ($fatherIds as $id) {
            $father = $this->repository->findId($id);
            if ($father === null || $father->gender !== 'male') {
                $name = $father->name;
                throw new BadRequestHttpException(null, [
                    'father_ids' => "{$name}(id=$id) должен быть мужского пола.",
                ]);
            }
        }
    }

    /**
     * Проверяет, что назначение родителей не создаст цикл в графе родства.
     *
     * Смысл: поднимаемся вверх от новых родителей по их mother_id/fathers
     * и смотрим, не встретим ли текущую кошку среди предков. Если встретим —
     * это цикл, операция запрещена.
     *
     * Актуально только для обновления: при создании id ещё нет.
     *
     * @param ?int $catId
     * @param ?int $motherId
     * @param array<int> $fatherIds
     * @return void
     */
    private function ensureNoCycle(
        ?int $catId,
        ?int $motherId,
        array $fatherIds,
    ): void {
        if ($catId === null) {
            return;
        }

        $ancestors = $this->collectAncestorIds($motherId, $fatherIds);

        if (isset($ancestors[$catId])) {
            throw new BadRequestHttpException(
                null,
                [
                    'mother_id' => 'Циклическая связь: кошка не может быть своим предком.',
                ]
            );
        }
    }

    /**
     * Собирает множество id всех предков, начиная с указанных родителей.
     *
     * Обход в ширину с защитой от повторного посещения: если в графе уже есть
     * цикл (испорченные данные), метод не зациклится, а вернёт то, что успел
     * собрать. Это делает его безопасным для вызова на «грязных» данных.
     *
     * @return array<int, true> карта id => true
     */
    private function collectAncestorIds(?int $motherId, array $fatherIds): array
    {
        // array_filter убирает null, если мать не указана.
        // array_merge собирает в один список мать + отцов.
        $stack = array_filter(array_merge([$motherId], $fatherIds));

        $visited = [];

        while ($stack !== []) {
            $id = array_pop($stack);

            if (isset($visited[$id])) {
                continue;
            }
            $visited[$id] = true;

            $parent = $this->repository->findId($id);
            if ($parent === null) {
                continue;
            }

            if ($parent->mother_id !== null) {
                $stack[] = $parent->mother_id;
            }
            foreach ($parent->fathers as $father) {
                $stack[] = $father->id;
            }
        }

        return $visited;
    }
}

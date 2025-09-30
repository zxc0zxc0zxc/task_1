<?php
declare(strict_types=1);

namespace App\Interfaces\Pipelines;

use App\Interfaces\DataProviders\DataProviderInterface;

interface DataProviderPipelineInterface
{
    /**
     * Получение следующего провайдера по приоритету
     * @return DataProviderPipelineInterface|null
     */
    public function next(): ?DataProviderPipelineInterface;

    /**
     * Резолв инстанса провайдера
     * @return DataProviderInterface
     */
    public function resolve(): DataProviderInterface;

    /**
     * Проверка на существование следующих по приоритету провайдеров
     * @return bool
     */
    public function canNext(): bool;
}

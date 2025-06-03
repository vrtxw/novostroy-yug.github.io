<?php

namespace Psr\Log;

/**
 * Описывает экземпляр логгера.
 */
interface LoggerInterface
{
    /**
     * Система непригодна для использования.
     */
    public function emergency($message, array $context = array());

    /**
     * Требуется немедленное действие.
     */
    public function alert($message, array $context = array());

    /**
     * Критические условия.
     */
    public function critical($message, array $context = array());

    /**
     * Ошибки времени выполнения, не требующие немедленных действий.
     */
    public function error($message, array $context = array());

    /**
     * Исключительные случаи, не являющиеся ошибками.
     */
    public function warning($message, array $context = array());

    /**
     * Нормальные, но важные события.
     */
    public function notice($message, array $context = array());

    /**
     * Интересные события.
     */
    public function info($message, array $context = array());

    /**
     * Подробная отладочная информация.
     */
    public function debug($message, array $context = array());

    /**
     * Логирование с произвольным уровнем.
     */
    public function log($level, $message, array $context = array());
} 
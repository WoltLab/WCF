<?php

declare(strict_types=1);

namespace Laminas\HttpHandlerRunner\Emitter;

use Laminas\HttpHandlerRunner\Exception;
use Psr\Http\Message\ResponseInterface;
use ReturnTypeWillChange;
use SplStack;

/**
 * Provides an EmitterInterface implementation that acts as a stack of Emitters.
 *
 * The implementations emit() method iterates itself.
 *
 * When iterating the stack, the first emitter to return a boolean
 * true value will short-circuit iteration.
 *
 * @template-extends SplStack<EmitterInterface>
 */
class EmitterStack extends SplStack implements EmitterInterface
{
    /**
     * Emit a response
     *
     * Loops through the stack, calling emit() on each; any that return a
     * boolean true value will short-circuit, skipping any remaining emitters
     * in the stack.
     *
     * As such, return a boolean false value from an emitter to indicate it
     * cannot emit the response, allowing the next emitter to try.
     */
    public function emit(ResponseInterface $response): bool
    {
        foreach ($this as $emitter) {
            if (false !== $emitter->emit($response)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Set an emitter on the stack by index.
     *
     * @param int $index
     * @param EmitterInterface $emitter
     * @return void
     * @throws Exception\InvalidEmitterException If not an EmitterInterface instance.
     */
    #[ReturnTypeWillChange]
    public function offsetSet($index, $emitter)
    {
        /** @psalm-suppress RedundantConditionGivenDocblockType */
        $this->validateEmitter($emitter);
        parent::offsetSet($index, $emitter);
    }

    /**
     * Push an emitter to the stack.
     *
     * @param EmitterInterface $emitter
     * @return void
     * @throws Exception\InvalidEmitterException If not an EmitterInterface instance.
     */
    #[ReturnTypeWillChange]
    public function push($emitter)
    {
        /** @psalm-suppress RedundantConditionGivenDocblockType */
        $this->validateEmitter($emitter);
        parent::push($emitter);
    }

    /**
     * Unshift an emitter to the stack.
     *
     * @param EmitterInterface $emitter
     * @return void
     * @throws Exception\InvalidEmitterException If not an EmitterInterface instance.
     */
    #[ReturnTypeWillChange]
    public function unshift($emitter)
    {
        /** @psalm-suppress RedundantConditionGivenDocblockType */
        $this->validateEmitter($emitter);
        parent::unshift($emitter);
    }

    /**
     * Validate that an emitter implements EmitterInterface.
     *
     * @param mixed $emitter
     * @throws Exception\InvalidEmitterException For non-emitter instances.
     * @psalm-assert EmitterInterface $emitter
     */
    private function validateEmitter($emitter): void
    {
        if (! $emitter instanceof EmitterInterface) {
            throw Exception\InvalidEmitterException::forEmitter($emitter);
        }
    }
}

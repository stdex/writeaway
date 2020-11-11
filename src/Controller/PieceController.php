<?php

declare(strict_types=1);

namespace Spiral\WriteAway\Controller;

use Spiral\Http\Exception\ClientException\ServerErrorException;
use Spiral\Logger\Traits\LoggerTrait;
use Spiral\Router\Annotation\Route;
use Spiral\Translator\Traits\TranslatorTrait;
use Spiral\WriteAway\Request\Piece\BulkRequest;
use Spiral\WriteAway\Request\Piece\DataRequest;
use Spiral\WriteAway\Request\Piece\PieceRequest;
use Spiral\WriteAway\Service\Pieces;

class PieceController
{
    use LoggerTrait;
    use TranslatorTrait;

    private Pieces $pieces;

    public function __construct(Pieces $pieces)
    {
        $this->pieces = $pieces;
    }

    /**
     * @Route(name="writeAway:pieces:save", group="writeAway", methods="POST", route="pieces/save")
     * @param PieceRequest $pieceRequest
     * @param DataRequest  $dataRequest
     * @return array
     */
    public function save(PieceRequest $pieceRequest, DataRequest $dataRequest): array
    {
        $piece = $this->pieces->get($pieceRequest->id());
        try {
            $this->pieces->save($piece, $dataRequest->data);
        } catch (\Throwable $exception) {
            $this->getLogger('default')->error('Piece update failed', compact('exception'));
            throw new ServerErrorException('Piece update failed', $exception);
        }

        return [
            'status' => 200,
            'data'   => $piece->pack()
        ];
    }

    /**
     * @Route(name="writeAway:pieces:get", group="writeAway", methods={"GET", "POST"}, route="pieces/get")
     * @param PieceRequest $request
     * @return array
     */
    public function get(PieceRequest $request): array
    {
        $piece = $this->pieces->get($request->id());

        return [
            'status' => 200,
            'data'   => $piece->pack()
        ];
    }

    /**
     * @Route(name="writeAway:pieces:bulk", group="writeAway", methods={"GET", "POST"}, route="pieces/bulk")
     * @param BulkRequest $request
     * @return array
     */
    public function bulk(BulkRequest $request): array
    {
        return [
            'status' => 200,
            'data'   => $this->pieces->getBulkList(...$request->ids())
        ];
    }
}

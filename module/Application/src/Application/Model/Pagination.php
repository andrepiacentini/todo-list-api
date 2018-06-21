<?php
/**
 * Created by PhpStorm.
 * User: murilo
 * Date: 3/28/17
 * Time: 4:53 PM
 */

namespace Application\Model;


use Illuminate\Database\Query\Builder;
use Symfony\Component\Config\Definition\Exception\Exception;

trait Pagination
{
    /**
     * @param Builder $query
     * @param int $qtdPerPage
     * @param int $page
     * @param string $order
     * @param string $direction
     * @return object
     */
    public static function paginated($query, $qtdPerPage = 5, $page = 1, $order = null, $direction = 'asc')
    {
        $total = count($query->get());
//        var_dump($total);exit;
        $totalPages = (int) ceil($total / $qtdPerPage);

        if ($totalPages < 1) {
            return (object) array(
                'total' => $total,
                'totalPages' => $totalPages,
                'qtdPerPage' => $qtdPerPage,
                'page' => $page,
                'data' => []
            );
        }

        if ($page > $totalPages) {
            throw new Exception('Inexistent page');
        }

        $data = $query->offset(($page - 1) * $qtdPerPage)
                        ->limit($qtdPerPage);

        if (!empty($order)) {
            if (empty($direction)) {
                $direction = 'asc';
            }
            $data = $data->orderBy($order, $direction);
        }

        $data = $data->get();

        return (object) array(
            'total' => $total,
            'totalPages' => $totalPages,
            'qtdPerPage' => $qtdPerPage,
            'page' => $page,
            'data' => $data
        );
    }
}
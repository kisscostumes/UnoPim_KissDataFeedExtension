<?php

namespace Webkul\KissDataFeed\DataGrids;

use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class CredentialDataGrid extends DataGrid
{
    public function prepareQueryBuilder()
    {
        $queryBuilder = DB::table('kiss_datafeed_credentials')
            ->select(
                'id',
                'api_url',
                'client_id',
                'active',
                'updated_at'
            );

        return $queryBuilder;
    }

    public function prepareColumns()
    {
        $this->addColumn([
            'index'      => 'api_url',
            'label'      => trans('kiss_datafeed::app.credentials.datagrid.api-url'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'client_id',
            'label'      => trans('kiss_datafeed::app.credentials.datagrid.client-id'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'active',
            'label'      => trans('kiss_datafeed::app.credentials.datagrid.active'),
            'type'       => 'boolean',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
            'closure'    => fn ($row) => $row->active
                ? '<span class="label-active">'.trans('admin::app.common.yes').'</span>'
                : '<span class="label-info">'.trans('admin::app.common.no').'</span>',
        ]);

        $this->addColumn([
            'index'      => 'updated_at',
            'label'      => trans('kiss_datafeed::app.credentials.datagrid.updated-at'),
            'type'       => 'datetime',
            'searchable' => false,
            'filterable' => true,
            'sortable'   => true,
        ]);
    }

    public function prepareActions()
    {
        if (bouncer()->hasPermission('kiss_datafeed.credentials.edit')) {
            $this->addAction([
                'icon'   => 'icon-edit',
                'title'  => trans('kiss_datafeed::app.credentials.datagrid.edit'),
                'method' => 'GET',
                'url'    => function ($row) {
                    return route('kiss_datafeed.credentials.edit', $row->id);
                },
            ]);
        }

        if (bouncer()->hasPermission('kiss_datafeed.mapping')) {
            $this->addAction([
                'icon'   => 'icon-export',
                'title'  => trans('kiss_datafeed::app.mapping.title'),
                'method' => 'GET',
                'url'    => function ($row) {
                    return route('kiss_datafeed.mapping.index', $row->id);
                },
            ]);
        }

        if (bouncer()->hasPermission('kiss_datafeed.credentials.delete')) {
            $this->addAction([
                'icon'   => 'icon-delete',
                'title'  => trans('kiss_datafeed::app.credentials.datagrid.delete'),
                'method' => 'DELETE',
                'url'    => function ($row) {
                    return route('kiss_datafeed.credentials.destroy', $row->id);
                },
            ]);
        }
    }
}

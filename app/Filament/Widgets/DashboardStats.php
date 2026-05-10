<?php

namespace App\Filament\Widgets;

use App\Models\Blog;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Filament\Schemas\Components\Grid;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Grid::make()->schema([
                Stat::make('Users', User::count())
                    ->description('Total users')
                    ->descriptionIcon(Heroicon::Users)
                    ->color('primary'),

                Stat::make('Products', Product::count())
                    ->description('Total products')
                    ->descriptionIcon(Heroicon::Cube)
                    ->color('success'),

                Stat::make('Categories', Category::count())
                    ->description('Total categories')
                    ->descriptionIcon(Heroicon::ViewColumns)
                    ->color('warning'),

                Stat::make('Blogs', Blog::count())
                    ->description('Total blogs')
                    ->descriptionIcon(Heroicon::InformationCircle)
                    ->color('info')
            ])->columnSpan(3),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Exports\StudentsExport;
use App\Filament\Resources\StudentResource\Pages;
use App\Filament\Resources\StudentResource\RelationManagers;
use App\Models\Classes;
use App\Models\Section;
use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Maatwebsite\Excel\Facades\Excel;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Academic Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('classes_id')
                    ->relationship('classes', 'name')
                    ->live()
                    ->required(),
                Forms\Components\Select::make('section_id')
                    ->options(function (Get $get) {
                        $classesId = $get('classes_id');
                        if ($classesId) {
                            return Section::where('classes_id', $classesId)->pluck('name', 'id')->toArray();
                        }
                    })
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('classes.name')
                    ->searchable()
                    ->badge(),
                Tables\Columns\TextColumn::make('section.name')
                    ->searchable()
                    ->badge(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('class-section-filter')
                    ->form([
                        Forms\Components\Select::make('classes_id')
                            ->label('filter by class')
                            ->placeholder('select a class')
                            ->options(
                                Classes::pluck('name', 'id')->toArray()
                            ),
                        Forms\Components\Select::make('section_id')
                            ->label('filter by section')
                            ->placeholder('select a section')
                            ->options(function (Get $get) {
                                $classesId = $get('classes_id');
                                return Section::where('classes_id', $classesId)->pluck('name', 'id')->toArray();
                            }),
                    ])
                    ->query(fn(Builder $query, array $data) => $query->when(
                        $data['classes_id'],
                        fn($query) => $query->where('classes_id', $data['classes_id'])
                    )->when(
                        $data['section_id'],
                        fn($query) => $query->where('section_id', $data['section_id'])
                    )),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('export')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function (Collection $records) {
                            return Excel::download(new StudentsExport($records), 'students.xlsx');
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::$model::count();
    }
}

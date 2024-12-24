

<h1>Ingredient Below Threshold</h1>
<p>The ingredient {{ $ingredient->name }} is below the {{ config('constants.INGREDIENT_STOCK_THRESHOLD') * 100 }}% threshold.</p>
<p>Current stock: {{ $ingredient->stock }}</p>
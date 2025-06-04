here relation is used like

YOu are dispalying table like


Sub-cat-name cate-name sub-cateslug

where cate-name from categories table than


Dont need to find name from id and pass cate name in view


just deginerelation in model


And fetch like below

$subcategory ->category ->cate_name

where

$subcategory - variable passed in view with sybcategory data
category - name of function used to define relation in model
cate_name - field name wants to display of category table



<td>{{ $subCategory->category->cate_name ?? 'N/A' }}</td>



like this relation is used
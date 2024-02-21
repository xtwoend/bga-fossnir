$query = Db::select("
                    select n.n as id, avg({$resultName}) as result,
                    ? + interval 2 * n.n hour as starttime,
                    ? + interval 2 * (n.n + 1) hour as endtime
                    from (
                        select 0 as n union all 
                        select 1 union all 
                        select 2 union all 
                        select 3 union all
                        select 4 union all 
                        select 5 union all 
                        select 6 union all
                        select 7 union all
                        select 8 union all
                        select 9 union all
                        select 10 union all
                        select 11 	
                        ) n left join
                        {$tableName}
                        on n.n = hour(sample_date) div 2
                    where 
                        sample_date between ? and ?
                    and
                        product_name in ('{$inParams}')
                    group by n.n
                    order by n.n;
                ", [$from, $to, $from, $to]);
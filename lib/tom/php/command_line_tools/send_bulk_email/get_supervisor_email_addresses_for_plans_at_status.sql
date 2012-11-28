SELECT DISTINCT(user_supervisor_pmo.email)
FROM plan
JOIN non_view_user_user_higher_duties_merged AS user_plan_owner ON (
    user_plan_owner.id=plan.user_id
)
LEFT JOIN user_pmo AS user_supervisor_pmo ON (
   user_supervisor_pmo.user_id = user_plan_owner.supervisor_id
)
WHERE user_plan_owner.deleted="0"
AND plan.plan_status_id IN (5, 6)
AND plan.plan_year_id=3
ORDER BY user_supervisor_pmo.email ASC

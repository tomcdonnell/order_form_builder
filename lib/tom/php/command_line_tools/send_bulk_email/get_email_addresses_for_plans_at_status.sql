SELECT user_pmo.email
FROM plan
JOIN non_view_user_user_higher_duties_merged AS user_plan_owner ON (user_plan_owner.id=plan.user_id)
JOIN user_pmo ON (user_pmo.user_id=user_plan_owner.id)
WHERE (user_plan_owner.deleted="0")
AND (plan.plan_status_id=7)
AND (plan.plan_year_id=3)
ORDER BY user_pmo.email ASC

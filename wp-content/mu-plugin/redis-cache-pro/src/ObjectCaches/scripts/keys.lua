
if redis.REDIS_VERSION == nil then
    redis.replicate_commands()
end

local command, patterns, cursor, total = nil, {}, 0, 0

if ARGV[#ARGV] == 'use-argv' then
    command = ARGV[2]
    table.insert(patterns, ARGV[1])
else
    command = ARGV[1]
    patterns = KEYS
end

for i, pattern in ipairs(patterns) do
    local keys = redis.call('KEYS', pattern)

    for j, key in ipairs(keys) do
        redis.call(command, key)
        total = total + 1
    end
end

return total

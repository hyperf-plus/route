#!/bin/bash

# HPlus Route 测试运行脚本
# 提供多种测试运行选项

set -e

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 显示帮助信息
show_help() {
    echo -e "${BLUE}HPlus Route 测试运行脚本${NC}"
    echo ""
    echo "用法: $0 [选项]"
    echo ""
    echo "选项:"
    echo "  -h, --help              显示此帮助信息"
    echo "  -a, --all               运行所有测试 (默认)"
    echo "  -u, --unit              只运行单元测试"
    echo "  -f, --feature           只运行功能测试"
    echo "  -c, --coverage          运行测试并生成覆盖率报告"
    echo "  -p, --performance       只运行性能测试"
    echo "  -g, --group GROUP       运行特定测试组"
    echo "  -v, --verbose           详细输出"
    echo "  --testdox               以文档格式显示测试结果"
    echo "  --list-groups           列出所有可用的测试组"
    echo ""
    echo "示例:"
    echo "  $0                      # 运行所有测试"
    echo "  $0 -u                   # 只运行单元测试"
    echo "  $0 -g performance       # 只运行性能测试"
    echo "  $0 -c                   # 生成覆盖率报告"
    echo "  $0 --testdox            # 文档格式输出"
}

# 检查PHPUnit是否存在
check_phpunit() {
    if [ ! -f "vendor/bin/phpunit" ]; then
        echo -e "${RED}错误: PHPUnit 未安装。请先运行 'composer install'${NC}"
        exit 1
    fi
}

# 运行测试的函数
run_tests() {
    local cmd="vendor/bin/phpunit"
    local args=""
    
    # 添加颜色支持
    args="$args --colors=always"
    
    # 根据参数添加选项
    case $1 in
        "all")
            echo -e "${GREEN}运行所有测试...${NC}"
            ;;
        "unit")
            echo -e "${GREEN}运行单元测试...${NC}"
            args="$args tests/Unit"
            ;;
        "feature")
            echo -e "${GREEN}运行功能测试...${NC}"
            args="$args tests/Feature"
            ;;
        "coverage")
            echo -e "${GREEN}运行测试并生成覆盖率报告...${NC}"
            args="$args --coverage-html coverage"
            ;;
        "performance")
            echo -e "${GREEN}运行性能测试...${NC}"
            args="$args --group performance"
            ;;
        "group")
            echo -e "${GREEN}运行测试组: $2${NC}"
            args="$args --group $2"
            ;;
        "verbose")
            echo -e "${GREEN}详细模式运行测试...${NC}"
            args="$args --verbose"
            ;;
        "testdox")
            echo -e "${GREEN}文档格式运行测试...${NC}"
            args="$args --testdox"
            ;;
        "list-groups")
            echo -e "${GREEN}列出所有测试组...${NC}"
            args="$args --list-groups"
            ;;
    esac
    
    # 执行命令
    echo -e "${YELLOW}执行命令: $cmd $args${NC}"
    echo ""
    
    $cmd $args
    
    local exit_code=$?
    
    if [ $exit_code -eq 0 ]; then
        echo ""
        echo -e "${GREEN}✅ 测试运行成功！${NC}"
        
        if [ "$1" = "coverage" ]; then
            echo -e "${BLUE}📊 覆盖率报告已生成到 coverage/ 目录${NC}"
        fi
    else
        echo ""
        echo -e "${RED}❌ 测试运行失败！退出代码: $exit_code${NC}"
        exit $exit_code
    fi
}

# 主逻辑
main() {
    check_phpunit
    
    # 如果没有参数，显示帮助
    if [ $# -eq 0 ]; then
        run_tests "all"
        return
    fi
    
    # 解析参数
    while [[ $# -gt 0 ]]; do
        case $1 in
            -h|--help)
                show_help
                exit 0
                ;;
            -a|--all)
                run_tests "all"
                exit 0
                ;;
            -u|--unit)
                run_tests "unit"
                exit 0
                ;;
            -f|--feature)
                run_tests "feature"
                exit 0
                ;;
            -c|--coverage)
                run_tests "coverage"
                exit 0
                ;;
            -p|--performance)
                run_tests "performance"
                exit 0
                ;;
            -g|--group)
                if [ -z "$2" ]; then
                    echo -e "${RED}错误: --group 需要指定组名${NC}"
                    exit 1
                fi
                run_tests "group" "$2"
                exit 0
                ;;
            -v|--verbose)
                run_tests "verbose"
                exit 0
                ;;
            --testdox)
                run_tests "testdox"
                exit 0
                ;;
            --list-groups)
                run_tests "list-groups"
                exit 0
                ;;
            *)
                echo -e "${RED}未知选项: $1${NC}"
                echo "使用 -h 或 --help 查看帮助信息"
                exit 1
                ;;
        esac
        shift
    done
}

# 运行主函数
main "$@" 